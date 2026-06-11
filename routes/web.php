<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Tenant\SwitchTenantController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

if (config('dev.one_click_login')) {
    require __DIR__.'/dev.php';
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('tenant/switch/{tenant}', SwitchTenantController::class)->name('tenant.switch');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin-web.php';
