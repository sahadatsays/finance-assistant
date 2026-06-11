<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SwaggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole() && ! config('swagger.ui_enabled', false)) {
            config([
                'l5-swagger.defaults.generate_always' => false,
            ]);
        }
    }
}
