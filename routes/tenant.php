<?php

use App\Http\Controllers\Api\Tenant\TenantController;
use App\Http\Controllers\Api\Tenant\TenantSettingsController;
use App\Http\Controllers\Api\Tenant\TenantSubscriptionController;
use App\Http\Controllers\Api\Tenant\TenantUserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/tenants')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function (): void {
        Route::get('/', [TenantController::class, 'index'])->name('api.tenants.index');

        Route::middleware('tenant.member')->group(function (): void {
            Route::get('{tenant}', [TenantController::class, 'show'])->name('api.tenants.show');
            Route::get('{tenant}/subscription', [TenantSubscriptionController::class, 'show'])
                ->name('api.tenants.subscription.show');

            Route::middleware('tenant.owner')->group(function (): void {
                Route::get('{tenant}/settings', [TenantSettingsController::class, 'show'])
                    ->name('api.tenants.settings.show');
                Route::patch('{tenant}/settings', [TenantSettingsController::class, 'update'])
                    ->name('api.tenants.settings.update');

                Route::get('{tenant}/users', [TenantUserController::class, 'index'])
                    ->name('api.tenants.users.index');
                Route::post('{tenant}/users', [TenantUserController::class, 'store'])
                    ->name('api.tenants.users.store');
                Route::patch('{tenant}/users/{user}', [TenantUserController::class, 'update'])
                    ->name('api.tenants.users.update');
                Route::delete('{tenant}/users/{user}', [TenantUserController::class, 'destroy'])
                    ->name('api.tenants.users.destroy');
            });
        });
    });
