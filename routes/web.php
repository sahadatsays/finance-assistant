<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

if (config('dev.one_click_login')) {
    require __DIR__.'/dev.php';
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin-web.php';
