<?php

use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    config(['dev.one_click_login' => true]);

    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
});

test('login page includes one click login accounts when enabled', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/login')
            ->where('oneClickLogin.enabled', true)
            ->has('oneClickLogin.accounts', 7)
            ->where('oneClickLogin.accounts.0.email', 'admin@financeassistant.com'));
});

test('super admin can one click login to admin dashboard', function () {
    $admin = User::query()->where('email', 'admin@financeassistant.com')->firstOrFail();

    $this->post(route('dev.login', $admin))
        ->assertRedirect(route('admin.dashboard', absolute: false));

    $this->assertAuthenticatedAs($admin);
});

test('tenant owner can one click login to user dashboard', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->post(route('dev.login', $owner))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($owner);
});

test('one click login is unavailable when disabled', function () {
    config(['dev.one_click_login' => false]);

    $user = User::query()->where('email', 'admin@financeassistant.com')->firstOrFail();

    $this->post(route('dev.login', $user))
        ->assertNotFound();
});

test('one click login rejects users outside dev account list', function () {
    $user = User::factory()->create(['email' => 'random@example.com']);

    $this->post(route('dev.login', $user))
        ->assertNotFound();
});

test('authenticated users cannot use one click login', function () {
    $guest = User::factory()->create();
    $admin = User::query()->where('email', 'admin@financeassistant.com')->firstOrFail();

    $this->actingAs($guest)
        ->post(route('dev.login', $admin))
        ->assertRedirect(route('dashboard', absolute: false));
});
