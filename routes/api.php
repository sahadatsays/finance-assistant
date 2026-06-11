<?php

use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\LoginHistoryController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api')->group(function (): void {
    Route::prefix('v1')->group(function (): void {
        require __DIR__.'/api/v1/foundation.php';
        require __DIR__.'/api/v1/auth.php';
        require __DIR__.'/api/v1/dashboard.php';
        require __DIR__.'/api/v1/categories.php';
        require __DIR__.'/api/v1/transactions.php';
        require __DIR__.'/api/v1/budgets.php';

        Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
            Route::get('devices', [DeviceController::class, 'index'])->name('api.devices.index');
            Route::patch('devices/{device}', [DeviceController::class, 'update'])->name('api.devices.update');
            Route::delete('devices/{device}', [DeviceController::class, 'destroy'])->name('api.devices.destroy');

            Route::get('sessions', [SessionController::class, 'index'])->name('api.sessions.index');
            Route::delete('sessions/others', [SessionController::class, 'destroyOthers'])->name('api.sessions.destroy-others');
            Route::delete('sessions/{session}', [SessionController::class, 'destroy'])->name('api.sessions.destroy');

            Route::get('login-history', [LoginHistoryController::class, 'index'])->name('api.login-history.index');
        });
    });

    require __DIR__.'/admin.php';
    require __DIR__.'/tenant.php';
});
