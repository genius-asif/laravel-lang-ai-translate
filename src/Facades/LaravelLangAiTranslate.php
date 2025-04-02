<?php

namespace GeniusAsif\LaravelLangAiTranslate\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GeniusAsif\LaravelLangAiTranslate\LaravelLangAiTranslate
 */
class LaravelLangAiTranslate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \GeniusAsif\LaravelLangAiTranslate\LaravelLangAiTranslate::class;
    }
}
