<?php

use App\Http\Controllers\Api\V1\MobileSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->prefix('sync')->group(function (): void {
    Route::get('transactions', [MobileSyncController::class, 'transactions'])->name('api.sync.transactions');
    Route::get('budgets', [MobileSyncController::class, 'budgets'])->name('api.sync.budgets');
    Route::get('goals', [MobileSyncController::class, 'goals'])->name('api.sync.goals');
    Route::get('dashboard', [MobileSyncController::class, 'dashboard'])->name('api.sync.dashboard');
    Route::get('notifications', [MobileSyncController::class, 'notifications'])->name('api.sync.notifications');
});
