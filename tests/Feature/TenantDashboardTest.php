<?php

use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use Database\Seeders\FinanceDemoSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(FinanceDemoSeeder::class);
});

test('tenant owner can view finance dashboard with metrics', function () {
    $owner = User::query()->where('email', 'owner@acme.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->has('tenant', fn ($tenant) => $tenant
                ->where('slug', 'acme-corp')
                ->etc()
            )
            ->has('metrics', fn ($metrics) => $metrics
                ->where('total_income', 6400)
                ->etc()
            )
            ->has('charts.income_vs_expense')
            ->has('charts.category_breakdown')
            ->has('charts.monthly_trend')
            ->has('widgets.recent_transactions')
            ->has('widgets.budget_alerts')
            ->has('widgets.savings_goals'));
});

test('tenant member can view dashboard for their tenant', function () {
    $member = User::query()->where('email', 'member@acme.com')->firstOrFail();

    $this->actingAs($member)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('tenant.slug', 'acme-corp'));
});

test('guest user without tenant sees empty dashboard state', function () {
    $guest = User::query()->where('email', 'guest@example.com')->firstOrFail();

    $this->actingAs($guest)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('dashboard')
            ->where('tenant', null)
            ->where('metrics', null));
});

test('user can switch between tenants', function () {
    $owner = User::query()->where('email', 'owner@startup.com')->firstOrFail();
    $startup = Tenant::query()->where('slug', 'startup-inc')->firstOrFail();

    $this->actingAs($owner)
        ->post(route('tenant.switch', $startup))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page->where('tenant.slug', 'startup-inc'));
});

test('finance data is scoped to current tenant', function () {
    $acme = Tenant::query()->where('slug', 'acme-corp')->firstOrFail();

    expect(Transaction::query()->where('tenant_id', $acme->id)->count())->toBeGreaterThan(0);
});

test('suspended tenant owner sees empty dashboard', function () {
    $owner = User::query()->where('email', 'owner@suspended.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('tenant', null));
});
