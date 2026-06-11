<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantStatus;

beforeEach(function () {
    Plan::factory()->create(['slug' => 'free', 'price_monthly' => 0, 'is_active' => true]);
    Plan::factory()->create(['slug' => 'pro', 'price_monthly' => 9.99, 'is_active' => true]);
});

test('super admin can view dashboard page', function () {
    $admin = User::factory()->platformAdmin()->create();
    Tenant::factory()->count(2)->create(['status' => TenantStatus::Active]);
    Tenant::factory()->trial()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard')
            ->has('metrics', fn ($metrics) => $metrics
                ->where('total_tenants', 3)
                ->where('active_tenants', 2)
                ->where('trial_tenants', 1)
                ->etc()
            )
            ->has('charts.growth')
            ->has('charts.registrations')
            ->has('charts.revenue')
            ->has('charts.tenant_statistics'));
});

test('super admin can access dashboard api', function () {
    $admin = User::factory()->platformAdmin()->create();
    $token = $admin->createToken('admin');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.admin.dashboard'))
        ->assertSuccessful()
        ->assertJsonStructure([
            'metrics' => [
                'total_tenants',
                'active_tenants',
                'trial_tenants',
                'revenue',
                'total_users',
                'new_registrations',
            ],
            'charts' => [
                'growth',
                'registrations',
                'revenue',
                'tenant_statistics',
            ],
        ]);
});

test('non admin cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('super admin can view tenant management page', function () {
    $admin = User::factory()->platformAdmin()->create();
    Tenant::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.tenants.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/tenants/index'));
});

test('super admin can view plans settings and activity logs pages', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)->get(route('admin.plans.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.activity-logs.index'))->assertOk();
});

test('tenant creation logs activity', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)->post(route('admin.tenants.store'), [
        'name' => 'Logged Tenant',
        'owner_email' => 'owner@logged.com',
    ])->assertRedirect(route('admin.tenants.index'));

    $this->assertDatabaseHas('activity_logs', [
        'description' => 'Tenant "Logged Tenant" was created.',
    ]);
});
