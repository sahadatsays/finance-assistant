<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\VersionController;
use Illuminate\Support\Facades\Route;

Route::get('/', VersionController::class)->name('api.v1.version');
Route::get('health', HealthController::class)->name('api.v1.health');
