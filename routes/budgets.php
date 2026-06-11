<?php

use App\Http\Controllers\Finance\BudgetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('budgets')
    ->name('budgets.')
    ->group(function (): void {
        Route::get('/', [BudgetController::class, 'index'])->name('index');
        Route::get('/export', [BudgetController::class, 'export'])->name('export');
        Route::post('/', [BudgetController::class, 'store'])->name('store');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('update');
        Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('destroy');
    });
