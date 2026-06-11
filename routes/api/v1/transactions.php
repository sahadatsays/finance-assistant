<?php

use App\Http\Controllers\Api\V1\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('transactions', [TransactionController::class, 'index'])->name('api.transactions.index');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])
        ->whereNumber('transaction')
        ->name('api.transactions.show');
    Route::post('transactions', [TransactionController::class, 'store'])->name('api.transactions.store');
    Route::put('transactions/{transaction}', [TransactionController::class, 'update'])
        ->whereNumber('transaction')
        ->name('api.transactions.update');
    Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])
        ->whereNumber('transaction')
        ->name('api.transactions.destroy');
});
