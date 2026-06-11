<?php

use App\Modules\Finance\FinanceServiceProvider;
use App\Modules\Tenant\TenantServiceProvider;
use App\Providers\ApiServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\SwaggerServiceProvider;

return [
    FinanceServiceProvider::class,
    TenantServiceProvider::class,
    ApiServiceProvider::class,
    AppServiceProvider::class,
    AuthServiceProvider::class,
    FortifyServiceProvider::class,
    SwaggerServiceProvider::class,
];
