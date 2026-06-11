<?php

use App\Http\Controllers\Admin\ActivityLogPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TenantPageController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'platform-admin'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('dashboard', DashboardController::class);

        Route::get('tenants', [TenantPageController::class, 'index'])->name('tenants.index');
        Route::post('tenants', [TenantPageController::class, 'store'])->name('tenants.store');
        Route::post('tenants/{tenant}/suspend', [TenantPageController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantPageController::class, 'activate'])->name('tenants.activate');

        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
        Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SystemSettingsController::class, 'update'])->name('settings.update');

        Route::get('activity-logs', [ActivityLogPageController::class, 'index'])->name('activity-logs.index');
    });
