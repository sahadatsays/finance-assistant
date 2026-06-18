<?php

use App\Models\Platform\Plan;
use App\Models\Platform\PlatformSetting;
use App\Models\Platform\Tenant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductionSeeder;

beforeEach(function () {
    config([
        'seeding.production.admin_name' => 'Production Admin',
        'seeding.production.admin_email' => 'admin@production.test',
        'seeding.production.admin_password' => 'secure-production-password',
    ]);
});

test('production seeder creates super admin and platform data without demo tenants', function () {
    $this->seed(ProductionSeeder::class);

    $admin = User::query()->where('email', 'admin@production.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->isPlatformAdmin())->toBeTrue()
        ->and($admin->name)->toBe('Production Admin')
        ->and(Plan::query()->count())->toBe(3)
        ->and(PlatformSetting::query()->where('key', 'app_name')->exists())->toBeTrue()
        ->and(Tenant::query()->count())->toBe(0)
        ->and(User::query()->count())->toBe(1);
});

test('production seeder is idempotent for super admin', function () {
    $this->seed(ProductionSeeder::class);
    $this->seed(ProductionSeeder::class);

    expect(User::query()->count())->toBe(1);
});

test('production seeder requires a password', function () {
    config(['seeding.production.admin_password' => null]);

    $this->seed(ProductionSeeder::class);
})->throws(RuntimeException::class, 'PRODUCTION_ADMIN_PASSWORD must be set');

test('production seeder requires a password with at least twelve characters', function () {
    config(['seeding.production.admin_password' => 'short']);

    $this->seed(ProductionSeeder::class);
})->throws(RuntimeException::class, 'PRODUCTION_ADMIN_PASSWORD must be at least 12 characters');

test('database seeder uses production seeder in production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    app(DatabaseSeeder::class)->run();

    expect(User::query()->where('email', 'admin@production.test')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'owner@acme.com')->exists())->toBeFalse()
        ->and(Tenant::query()->count())->toBe(0);
});

test('database seeder uses demo seeders outside production environment', function () {
    app()->detectEnvironment(fn () => 'local');

    app(DatabaseSeeder::class)->run();

    expect(User::query()->where('email', 'admin@financeassistant.com')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'owner@acme.com')->exists())->toBeTrue()
        ->and(Tenant::query()->count())->toBe(3);
});
