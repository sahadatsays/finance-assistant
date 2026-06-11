<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

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

        Route::get('profile', [ProfileController::class, 'show'])->name('api.auth.profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('api.auth.profile.update');

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
