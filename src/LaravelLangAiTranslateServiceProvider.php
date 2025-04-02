<?php

namespace GeniusAsif\LaravelLangAiTranslate;

use GeniusAsif\LaravelLangAiTranslate\Commands\LaravelLangAiTranslateCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelLangAiTranslateServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-lang-ai-translate')
            ->hasCommand(LaravelLangAiTranslateCommand::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/lang-ai-translation.php' => config_path('lang-ai-translation.php'),
            ], 'lang-ai-translation');
        }
    }
}
