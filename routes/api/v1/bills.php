<?php

use App\Http\Controllers\Api\V1\BillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('bills/upcoming', [BillController::class, 'upcoming'])->name('api.bills.upcoming');
    Route::get('bills', [BillController::class, 'index'])->name('api.bills.index');
    Route::post('bills', [BillController::class, 'store'])->name('api.bills.store');
    Route::put('bills/{bill}', [BillController::class, 'update'])
        ->whereNumber('bill')
        ->name('api.bills.update');
    Route::delete('bills/{bill}', [BillController::class, 'destroy'])
        ->whereNumber('bill')
        ->name('api.bills.destroy');
    Route::post('bills/{bill}/mark-paid', [BillController::class, 'markPaid'])
        ->whereNumber('bill')
        ->name('api.bills.mark-paid');
});
