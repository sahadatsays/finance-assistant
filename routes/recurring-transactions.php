<?php

use App\Http\Controllers\Finance\RecurringTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('recurring-transactions')
    ->name('recurring-transactions.')
    ->group(function (): void {
        Route::get('/', [RecurringTransactionController::class, 'index'])->name('index');
        Route::post('/', [RecurringTransactionController::class, 'store'])->name('store');
        Route::put('/{recurringTransaction}', [RecurringTransactionController::class, 'update'])->name('update');
        Route::delete('/{recurringTransaction}', [RecurringTransactionController::class, 'destroy'])->name('destroy');
        Route::post('/{recurringTransaction}/resume', [RecurringTransactionController::class, 'resume'])->name('resume');
    });
