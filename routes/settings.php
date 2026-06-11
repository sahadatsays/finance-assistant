<?php

use App\Http\Controllers\Settings\DeviceController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SessionController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');

    Route::get('settings/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::patch('settings/devices/{device}', [DeviceController::class, 'update'])->name('devices.update');
    Route::delete('settings/devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('settings/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('settings/sessions/others', [SessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
    Route::delete('settings/sessions/{session}', [SessionController::class, 'destroy'])->name('sessions.destroy');
});
