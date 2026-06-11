<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])
        ->whereNumber('category')
        ->name('api.categories.show');
    Route::post('categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])
        ->whereNumber('category')
        ->name('api.categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])
        ->whereNumber('category')
        ->name('api.categories.destroy');
});
