<?php

use App\Http\Controllers\Finance\GoalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('goals')
    ->name('goals.')
    ->group(function (): void {
        Route::get('/', [GoalController::class, 'index'])->name('index');
        Route::get('/export', [GoalController::class, 'export'])->name('export');
        Route::post('/', [GoalController::class, 'store'])->name('store');
        Route::put('/{goal}', [GoalController::class, 'update'])->name('update');
        Route::delete('/{goal}', [GoalController::class, 'destroy'])->name('destroy');
        Route::post('/{goal}/contributions', [GoalController::class, 'contribute'])->name('contributions.store');
        Route::delete('/{goal}/contributions/{contribution}', [GoalController::class, 'destroyContribution'])->name('contributions.destroy');
    });
