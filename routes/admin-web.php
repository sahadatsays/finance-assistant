<?php

use App\Http\Controllers\Admin\ActivityLogPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\TenantPageController;
use App\Http\Controllers\Admin\Website\BlogPostController;
use App\Http\Controllers\Admin\Website\FaqController;
use App\Http\Controllers\Admin\Website\FooterSettingController;
use App\Http\Controllers\Admin\Website\HomepageController;
use App\Http\Controllers\Admin\Website\MediaAssetController;
use App\Http\Controllers\Admin\Website\NavigationItemController;
use App\Http\Controllers\Admin\Website\SeoEntryController;
use App\Http\Controllers\Admin\Website\TestimonialController;
use App\Http\Controllers\Admin\Website\WebsiteDashboardController;
use App\Http\Controllers\Admin\Website\WebsitePageController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'platform-admin'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('dashboard', DashboardController::class);

        Route::get('tenants', [TenantPageController::class, 'index'])->name('tenants.index');
        Route::post('tenants', [TenantPageController::class, 'store'])->name('tenants.store');
        Route::post('tenants/{tenant}/suspend', [TenantPageController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantPageController::class, 'activate'])->name('tenants.activate');

        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
        Route::patch('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        Route::get('settings', [SystemSettingsController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SystemSettingsController::class, 'update'])->name('settings.update');

        Route::get('activity-logs', [ActivityLogPageController::class, 'index'])->name('activity-logs.index');

        Route::prefix('website')->name('website.')->group(function (): void {
            Route::get('/', WebsiteDashboardController::class)->name('index');

            Route::get('homepage', [HomepageController::class, 'index'])->name('homepage.index');
            Route::patch('homepage', [HomepageController::class, 'update'])->name('homepage.update');

            Route::get('pages', [WebsitePageController::class, 'index'])->name('pages.index');
            Route::post('pages', [WebsitePageController::class, 'store'])->name('pages.store');
            Route::patch('pages/{websitePage}', [WebsitePageController::class, 'update'])->name('pages.update');
            Route::delete('pages/{websitePage}', [WebsitePageController::class, 'destroy'])->name('pages.destroy');

            Route::get('navigation', [NavigationItemController::class, 'index'])->name('navigation.index');
            Route::post('navigation', [NavigationItemController::class, 'store'])->name('navigation.store');
            Route::patch('navigation/{navigationItem}', [NavigationItemController::class, 'update'])->name('navigation.update');
            Route::post('navigation/reorder', [NavigationItemController::class, 'reorder'])->name('navigation.reorder');
            Route::delete('navigation/{navigationItem}', [NavigationItemController::class, 'destroy'])->name('navigation.destroy');

            Route::get('footer', [FooterSettingController::class, 'index'])->name('footer.index');
            Route::patch('footer/settings', [FooterSettingController::class, 'updateSettings'])->name('footer.settings.update');
            Route::post('footer/links', [FooterSettingController::class, 'storeLink'])->name('footer.links.store');

            Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
            Route::post('testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
            Route::patch('testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
            Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

            Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
            Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
            Route::patch('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

            Route::get('plans', [PlanController::class, 'websiteIndex'])->name('plans.index');
            Route::post('plans/reorder', [PlanController::class, 'reorder'])->name('plans.reorder');

            Route::get('blog', [BlogPostController::class, 'index'])->name('blog.index');
            Route::get('blog/create', [BlogPostController::class, 'create'])->name('blog.create');
            Route::post('blog', [BlogPostController::class, 'store'])->name('blog.store');
            Route::get('blog/{blogPost}/edit', [BlogPostController::class, 'edit'])->name('blog.edit');
            Route::patch('blog/{blogPost}', [BlogPostController::class, 'update'])->name('blog.update');
            Route::post('blog/{blogPost}/publish', [BlogPostController::class, 'publish'])->name('blog.publish');
            Route::post('blog/{blogPost}/unpublish', [BlogPostController::class, 'unpublish'])->name('blog.unpublish');
            Route::delete('blog/{blogPost}', [BlogPostController::class, 'destroy'])->name('blog.destroy');

            Route::get('seo', [SeoEntryController::class, 'index'])->name('seo.index');
            Route::patch('seo/{seoEntry}', [SeoEntryController::class, 'update'])->name('seo.update');

            Route::get('media', [MediaAssetController::class, 'index'])->name('media.index');
            Route::post('media', [MediaAssetController::class, 'store'])->name('media.store');
            Route::delete('media/{mediaAsset}', [MediaAssetController::class, 'destroy'])->name('media.destroy');
        });
    });
