<?php

use App\Http\Controllers\Api\V1\AttachmentController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('attachments/{attachment}/file', [AttachmentController::class, 'file'])
    ->middleware('signed')
    ->whereNumber('attachment')
    ->name('api.attachments.file');

Route::middleware(['auth:sanctum', 'verified'])->group(function (): void {
    Route::post('uploads', [UploadController::class, 'store'])->name('api.uploads.store');
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])
        ->whereNumber('attachment')
        ->name('api.attachments.show');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->whereNumber('attachment')
        ->name('api.attachments.destroy');
    Route::post('transactions/{transaction}/attachments', [AttachmentController::class, 'storeForTransaction'])
        ->whereNumber('transaction')
        ->name('api.transactions.attachments.store');
});
