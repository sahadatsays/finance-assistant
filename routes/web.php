<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\SwitchTenantController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/marketing.php';

if (config('dev.one_click_login')) {
    require __DIR__.'/dev.php';
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('tenant/switch/{tenant}', SwitchTenantController::class)->name('tenant.switch');
});

require __DIR__.'/settings.php';
require __DIR__.'/categories.php';
require __DIR__.'/accounts.php';
require __DIR__.'/transactions.php';
require __DIR__.'/budgets.php';
require __DIR__.'/goals.php';
require __DIR__.'/admin-web.php';
