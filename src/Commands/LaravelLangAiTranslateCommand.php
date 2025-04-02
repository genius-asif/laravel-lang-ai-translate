<?php

declare(strict_types=1);

namespace GeniusAsif\LaravelLangAiTranslate\Commands;

use GeniusAsif\LaravelLangAiTranslate\Enums\ApiProvider;
use GeniusAsif\LaravelLangAiTranslate\LaravelLangAiTranslate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;

class LaravelLangAiTranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:lang';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Automatically translate the language files from 'english' to mentioned language using LLM APIs";

    /**
     * Execute the console command.
     */
    public function handle(LaravelLangAiTranslate $translator)
    {
        $langPath = lang_path();

        if (! File::isDirectory($langPath)) {
            error("❌ 'lang' directory not found, please create it, if doesn't exist.");
            exit(1);
        }

        $langPath = lang_path('en');

        if (! File::isDirectory($langPath)) {
            error("❌ 'en' language directory not found, please create it, if doesn't exist.");
            exit(1);
        }

        $files = File::files($langPath);

        if (count($files) === 0) {
            error("❌ No language files found in the 'en' language directory.");
            exit(1);
        }

        $langKey = search(
            label: 'Search for the language that you want to translate in',
            placeholder: 'E.g. Spanish',
            options: fn (string $value) => strlen($value) > 0
                ? array_filter(Config::get('lang-ai-translation.languages', []), function ($lang) use ($value) {
                    return str_contains(strtolower($lang), strtolower($value));
                })
                : [],
            required: true
        );

        $language = Config::get('lang-ai-translation.languages', [])[$langKey];

        $apiProvider = select(
            label: 'Please select API provider',
            options: ApiProvider::options(),
            required: true
        );

        $apiKey = $translator->getApiKey($apiProvider);

        if (! $apiKey) {
            error("The required API key for $apiProvider is missing. Please add it to your .env file.");
            exit(1);
        }

        info("Starting translation for language: $language using $apiProvider...");

        progress(
            label: 'Translating language files...',
            steps: count($files),
            callback: function () use ($files, $language, $langKey, $apiProvider, $translator): void {
                foreach ($files as $file) {
                    $filename = $file->getFilenameWithoutExtension();
                    $filePath = $file->getPathname();

                    $translations = $translator->validateLanguageFile($filePath, $filename);

                    if ($translations === false) {
                        exit(1);
                    }

                    $success = $translator->translateFile($filename, $translations, $language, $apiProvider, $langKey);
                    if (! $success) {
                        exit(1);
                    }
                }
            }
        );

        info('✅ Language file translation has been successfully completed.');
    }
}
