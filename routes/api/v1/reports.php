<?php

use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::get('reports/summary', [ReportController::class, 'summary'])->name('api.reports.summary');
    Route::get('reports/monthly', [ReportController::class, 'monthly'])->name('api.reports.monthly');
    Route::get('reports/category', [ReportController::class, 'category'])->name('api.reports.category');
    Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('api.reports.cashflow');
    Route::get('reports/net-worth', [ReportController::class, 'netWorth'])->name('api.reports.net-worth');
    Route::post('reports/export', [ReportController::class, 'export'])->name('api.reports.export');
});
