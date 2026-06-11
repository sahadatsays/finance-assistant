<?php

use App\Http\Controllers\Auth\OneClickLoginController;
use Illuminate\Support\Facades\Route;

Route::post('dev/login/{user}', OneClickLoginController::class)
    ->middleware('guest')
    ->name('dev.login');
