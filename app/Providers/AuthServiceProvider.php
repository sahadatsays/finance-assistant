<?php

namespace App\Providers;

use App\Listeners\RecordFailedLoginAttempt;
use App\Listeners\RecordSuccessfulLogin;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            RecordSuccessfulLogin::class,
        ],
        Failed::class => [
            RecordFailedLoginAttempt::class,
        ],
    ];
}
