<?php

use App\Http\Controllers\Finance\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('transactions')
    ->name('transactions.')
    ->group(function (): void {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
        Route::post('/', [TransactionController::class, 'store'])->name('store');
        Route::put('/{transaction}', [TransactionController::class, 'update'])->name('update');
        Route::delete('/{transaction}', [TransactionController::class, 'destroy'])->name('destroy');
    });
