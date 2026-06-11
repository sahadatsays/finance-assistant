<?php

use App\Http\Controllers\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'verified', 'platform-admin'])
    ->group(function (): void {
        Route::get('tenants', [TenantController::class, 'index'])->name('api.admin.tenants.index');
        Route::post('tenants', [TenantController::class, 'store'])->name('api.admin.tenants.store');
        Route::get('tenants/{tenant}', [TenantController::class, 'show'])->name('api.admin.tenants.show');
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('api.admin.tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('api.admin.tenants.activate');
        Route::get('tenants/{tenant}/usage', [TenantController::class, 'usage'])->name('api.admin.tenants.usage');
        Route::patch('tenants/{tenant}/subscription', [TenantController::class, 'updateSubscription'])
            ->name('api.admin.tenants.subscription.update');
    });
