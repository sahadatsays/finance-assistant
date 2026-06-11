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
        ->assertSee('One-Click Login', false)
        ->assertSee('data-test="one-click-login"', false)
        ->assertSee('admin@financeassistant.com', false)
        ->assertSee('owner@acme.com', false)
        ->assertSee('member@acme.com', false)
        ->assertSee('owner@startup.com', false)
        ->assertSee('member@startup.com', false)
        ->assertSee('owner@suspended.com', false)
        ->assertSee('guest@example.com', false);
});

test('login page shows all role labels for seeded accounts', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Super Admin', false)
        ->assertSee('Tenant Owner', false)
        ->assertSee('Tenant User', false)
        ->assertSee('Guest', false);
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

test('tenant user can one click login to user dashboard', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->post(route('dev.login', $member))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($member);
});

test('guest user can one click login to user dashboard', function () {
    $guest = User::query()->where('email', 'guest@example.com')->firstOrFail();

    $this->post(route('dev.login', $guest))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($guest);
});

test('one click login is unavailable when disabled', function () {
    config(['dev.one_click_login' => false]);

    $user = User::query()->where('email', 'admin@financeassistant.com')->firstOrFail();

    $this->get(route('login'))->assertOk()->assertDontSee('One-Click Login', false);

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
