<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\NetWorthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('accounts', [AccountController::class, 'index'])->name('api.accounts.index');
    Route::post('accounts', [AccountController::class, 'store'])->name('api.accounts.store');
    Route::put('accounts/{account}', [AccountController::class, 'update'])
        ->whereNumber('account')
        ->name('api.accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])
        ->whereNumber('account')
        ->name('api.accounts.destroy');

    Route::get('net-worth', [NetWorthController::class, 'show'])->name('api.net-worth.show');
    Route::get('net-worth/history', [NetWorthController::class, 'history'])->name('api.net-worth.history');
});
