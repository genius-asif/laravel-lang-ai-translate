<?php

namespace GeniusAsif\LaravelLangAiTranslate\Enums;

enum ApiProvider: string
{
    case GOOGLE_GEMINI = 'Google Gemini';
    case OPENAI = 'OpenAI';
    case DEEPSEEK = 'DeepSeek';

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }
}
