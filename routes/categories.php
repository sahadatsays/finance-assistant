<?php

use App\Http\Controllers\Finance\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('categories')
    ->name('categories.')
    ->group(function (): void {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/archive', [CategoryController::class, 'archive'])->name('archive');
        Route::post('/{category}/restore', [CategoryController::class, 'restore'])->name('restore');
    });
