<?php

namespace SpykApp\FilamentPasswordlessLogin;

use Illuminate\Support\ServiceProvider;

class FilamentPasswordlessLoginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-passwordless-login.php',
            'filament-passwordless-login'
        );
    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'filament-passwordless-login'
        );

        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'filament-passwordless-login'
        );

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filament-passwordless-login.php' => config_path('filament-passwordless-login.php'),
            ], 'filament-passwordless-login-config');

            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/filament-passwordless-login'),
            ], 'filament-passwordless-login-lang');
        }
    }
}
