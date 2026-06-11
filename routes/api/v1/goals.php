<?php

use App\Http\Controllers\Api\V1\GoalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('goals', [GoalController::class, 'index'])->name('api.goals.index');
    Route::get('goals/{goal}', [GoalController::class, 'show'])
        ->whereNumber('goal')
        ->name('api.goals.show');
    Route::post('goals', [GoalController::class, 'store'])->name('api.goals.store');
    Route::put('goals/{goal}', [GoalController::class, 'update'])
        ->whereNumber('goal')
        ->name('api.goals.update');
    Route::delete('goals/{goal}', [GoalController::class, 'destroy'])
        ->whereNumber('goal')
        ->name('api.goals.destroy');
    Route::post('goals/{goal}/contribute', [GoalController::class, 'contribute'])
        ->whereNumber('goal')
        ->name('api.goals.contribute');
});
