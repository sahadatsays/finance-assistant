<?php

use App\Http\Controllers\Api\V1\InvestmentController;
use App\Http\Controllers\Api\V1\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('investments', [InvestmentController::class, 'index'])->name('api.investments.index');
    Route::post('investments', [InvestmentController::class, 'store'])->name('api.investments.store');
    Route::put('investments/{investment}', [InvestmentController::class, 'update'])
        ->whereNumber('investment')
        ->name('api.investments.update');
    Route::delete('investments/{investment}', [InvestmentController::class, 'destroy'])
        ->whereNumber('investment')
        ->name('api.investments.destroy');

    Route::get('portfolio/performance', [PortfolioController::class, 'performance'])->name('api.portfolio.performance');
    Route::get('portfolio/allocation', [PortfolioController::class, 'allocation'])->name('api.portfolio.allocation');
});
