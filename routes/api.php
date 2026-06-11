<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\LoginHistoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', RegisterController::class)
            ->middleware('throttle:api-auth')
            ->name('api.auth.register');

        Route::post('login', LoginController::class)
            ->middleware('throttle:api-auth')
            ->name('api.auth.login');

        Route::post('forgot-password', ForgotPasswordController::class)
            ->middleware('throttle:api-auth')
            ->name('api.auth.forgot-password');

        Route::post('reset-password', ResetPasswordController::class)
            ->middleware('throttle:api-auth')
            ->name('api.auth.reset-password');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('logout', LogoutController::class)->name('api.auth.logout');

            Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('api.auth.verification.verify');

            Route::post('email/resend', [EmailVerificationController::class, 'resend'])
                ->middleware('throttle:6,1')
                ->name('api.auth.verification.resend');

            Route::get('email/status', [EmailVerificationController::class, 'status'])
                ->name('api.auth.verification.status');
        });
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
        Route::get('profile', [ProfileController::class, 'show'])->name('api.profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('api.profile.update');

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
