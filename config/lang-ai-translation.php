<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Language Configuration
    |--------------------------------------------------------------------------
    |
    | This section defines the languages available for translation. Each key represents
    | the language code (e.g., 'hi', 'es'), and the corresponding value is the
    | human-readable language name (e.g., 'Hindi', 'Spanish'). These languages
    | will be used as options when prompting for the target translation language.
    |
    | Example:
    |
    | 'languages' => [
    |     'hi' => 'Hindi',
    |     'es' => 'Spanish',
    |     // ... more languages ...
    | ]
    |
    */
    'languages' => [
        'hi' => 'Hindi',
        'es' => 'Spanish',
        'fr' => 'French',
        'ur' => 'Urdu',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Provider Configuration
    |--------------------------------------------------------------------------
    |
    | This section configures the API providers used for language translation. Each
    | provider (e.g., 'google_gemini', 'openai', 'deepseek') requires an API key and
    | URL. The API keys are retrieved from the environment variables, and the URLs
    | are used to make requests to the respective translation services.
    |
    | Ensure that the necessary API keys and related URLs are set in your .env file.
    |
    */
    'providers' => [
        'google_gemini' => [
            'key' => config('GOOGLE_GEMINI_API_KEY', ''),
            'url' => config('GOOGLE_GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.config('GOOGLE_GEMINI_API_KEY')),
        ],
        'openai' => [
            'key' => config('OPENAI_API_KEY', ''),
            'url' => config('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
        ],
        'deepseek' => [
            'key' => config('DEEPSEEK_API_KEY', ''),
            'url' => config('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions'),
        ],
    ],
];
