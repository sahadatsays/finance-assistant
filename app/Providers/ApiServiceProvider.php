<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            $limit = $request->user()
                ? config('api.rate_limits.authenticated', 120)
                : config('api.rate_limits.guest', 60);

            return Limit::perMinute($limit)->by($request->user()?->id ?: $request->ip());
        });
    }
}
