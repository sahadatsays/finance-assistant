<?php

use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('api.notifications.read');
    Route::post('device-token', [DeviceTokenController::class, 'store'])->name('api.device-token.store');
    Route::delete('device-token', [DeviceTokenController::class, 'destroy'])->name('api.device-token.destroy');
});
