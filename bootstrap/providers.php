<?php

use App\Modules\Finance\FinanceServiceProvider;
use App\Modules\Tenant\TenantServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    AuthServiceProvider::class,
    FortifyServiceProvider::class,
    TenantServiceProvider::class,
    FinanceServiceProvider::class,
];
