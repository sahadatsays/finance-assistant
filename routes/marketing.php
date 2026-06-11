<?php

use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\MarketingController;
use App\Http\Controllers\Web\PricingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
Route::get('/pricing', PricingController::class)->name('marketing.pricing');
Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
Route::post('/contact', [ContactController::class, 'store'])->name('marketing.contact.store');
Route::get('/blog', [MarketingController::class, 'blog'])->name('marketing.blog');
Route::get('/blog/{slug}', [MarketingController::class, 'blogShow'])->name('marketing.blog.show');
Route::get('/help', [MarketingController::class, 'help'])->name('marketing.help');
Route::get('/help/{category}/{article}', [MarketingController::class, 'helpShow'])->name('marketing.help.show');
Route::get('/privacy', [MarketingController::class, 'privacy'])->name('marketing.privacy');
Route::get('/terms', [MarketingController::class, 'terms'])->name('marketing.terms');
