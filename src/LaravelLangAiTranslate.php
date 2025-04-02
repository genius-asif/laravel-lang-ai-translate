<?php

declare(strict_types=1);

namespace GeniusAsif\LaravelLangAiTranslate;

use GeniusAsif\LaravelLangAiTranslate\Enums\ApiProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class LaravelLangAiTranslate 
{
    public function validateLanguageFile($filePath, $filename)
    {
        try {
            $translations = include $filePath;

            if (!is_array($translations)) {
                error("❌ Invalid language file format: $filename.php does not return an array.");
                return false;
            }

            foreach ($translations as $key => $value) {
                if (!is_string($key) || (!is_string($value) && !is_array($value))) {
                    error("❌ Invalid key-value pair in $filename.php. Ensure all keys are strings and values are strings or arrays.");
                    return false;
                }
            }

            return $translations;

        } catch (\Throwable $e) {
            error("❌ Syntax error in $filename.php: " . $e->getMessage());
            return false;
        }
    }

    public function translateFile($filename, $translations, $targetLanguage, $apiProvider, $langKey)
    {
        $targetLangPath = lang_path($langKey);
        if (!File::isDirectory($targetLangPath)) {
            File::makeDirectory($targetLangPath, 0755, true);
        }

        $targetFilePath = $targetLangPath . '/' . $filename . '.php';
        $targetTranslations = [];

        foreach ($translations as $key => $value) {
            $translatedText = $this->translateText($value, $targetLanguage, $apiProvider);
            
            if ($translatedText === 'api error') {
                return false;
            }

            if (!$translatedText) {
                error("❌ Translation failed for key: '$key' in '$filename.php' (Stopping Process).");
                continue;
            }
            
            $targetTranslations[$key] = $translatedText;
        }

        $content = "<?php\n\nreturn " . var_export($targetTranslations, true) . ";\n";

        File::put($targetFilePath, $content);

        info("✅ Successfully translated '$filename.php' to '$targetLanguage'.");
        return true;
    }

    public function translateText($text, $targetLanguage, $apiProvider)
    {
        if (is_array($text)) {
            $translatedArray = [];
            foreach ($text as $key => $value) {
                $translatedArray[$key] = $this->translateText($value, $targetLanguage, $apiProvider);
            }
            return $translatedArray;
        }

        $apiKey = $this->getApiKey($apiProvider);

        if ($apiProvider === ApiProvider::GOOGLE_GEMINI->name) {
            $url = config('lang-ai-translation.providers.google_gemini.url');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => "Translate the following text from English to {$targetLanguage}: {$text} and only return the translated text."]
                        ]
                    ]
                ]
            ]);
    
            if ($response->failed()) {
                error("❌ Google Gemini API Error: " . $response->body());

                return 'api error';
            }

            $responseData = $response->json();

            return str_replace("\n", "", $responseData['candidates'][0]['content']['parts'][0]['text']);
        }

        if ($apiProvider === ApiProvider::OPENAI->name) {
            $url = config('lang-ai-translation.providers.openai.url');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful translator.'],
                    ['role' => 'user', 'content' => "Translate the following text from English to {$targetLanguage}: {$text}"]
                ],
                'max_tokens' => 100,
            ]);

            if ($response->failed()) {
                error("❌ OpenAI API Error: " . $response->body());

                return 'api error';
            }

            $responseData = $response->json();

            return str_replace("\n", "", $responseData['choices'][0]['message']['content']);
        }

        if ($apiProvider === ApiProvider::DEEPSEEK->name) {
            $url = config('lang-ai-translation.providers.deepseek.url');

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                    ['role' => 'user', 'content' => "Translate the following text from English to {$targetLanguage}: {$text} and only return the translated text."]
                ],
                'stream' => false,
            ]);

            if ($response->failed()) {
                error("❌ DeepSeek API Error: " . $response->body());

                return 'api error';
            }

            $responseData = $response->json();

            return str_replace("\n", "", $responseData['choices'][0]['message']['content']);
        }

        return null;
    }

    public function getApiKey($apiProvider)
    {        
        return match ($apiProvider) {
            ApiProvider::GOOGLE_GEMINI->name => config('lang-ai-translation.providers.google_gemini.key'),
            ApiProvider::OPENAI->name => config('lang-ai-translation.providers.openai.key'),
            ApiProvider::DEEPSEEK->name => config('lang-ai-translation.providers.deepseek.key'),
            default => null,
        };
    }
}
