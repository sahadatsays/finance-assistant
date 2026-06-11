<?php

use App\Http\Controllers\Api\V1\BudgetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('budgets', [BudgetController::class, 'index'])->name('api.budgets.index');
    Route::get('budgets/{budget}/analysis', [BudgetController::class, 'analysis'])
        ->whereNumber('budget')
        ->name('api.budgets.analysis');
    Route::get('budgets/{budget}', [BudgetController::class, 'show'])
        ->whereNumber('budget')
        ->name('api.budgets.show');
    Route::post('budgets', [BudgetController::class, 'store'])->name('api.budgets.store');
    Route::put('budgets/{budget}', [BudgetController::class, 'update'])
        ->whereNumber('budget')
        ->name('api.budgets.update');
    Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])
        ->whereNumber('budget')
        ->name('api.budgets.destroy');
});
