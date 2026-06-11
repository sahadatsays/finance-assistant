<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantUser;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantStatus;
use App\Modules\Tenant\Enums\TenantUserRole;

beforeEach(function () {
    Plan::factory()->create(['slug' => 'free', 'name' => 'Free', 'max_users' => 10, 'is_active' => true]);
    Plan::factory()->create(['slug' => 'pro', 'name' => 'Pro', 'max_users' => 25, 'is_active' => true]);
});

test('super admin can create a tenant with owner', function () {
    $admin = User::factory()->platformAdmin()->create();
    $token = $admin->createToken('admin');

    $response = $this->withToken($token->plainTextToken)
        ->postJson(route('api.admin.tenants.store'), [
            'name' => 'Acme Finance',
            'owner_email' => 'owner@acme.com',
            'owner_name' => 'Acme Owner',
        ]);

    $response->assertCreated()
        ->assertJsonPath('tenant.name', 'Acme Finance')
        ->assertJsonPath('tenant.status', TenantStatus::Trial->value);

    $this->assertDatabaseHas('tenants', ['name' => 'Acme Finance']);
    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => Tenant::query()->where('name', 'Acme Finance')->value('id'),
    ]);
    $this->assertDatabaseHas('tenant_users', [
        'role' => TenantUserRole::Owner->value,
    ]);
});

test('super admin can list tenants', function () {
    $admin = User::factory()->platformAdmin()->create();
    Tenant::factory()->count(3)->create();
    $token = $admin->createToken('admin');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.admin.tenants.index'))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

test('super admin can suspend and activate tenant', function () {
    $admin = User::factory()->platformAdmin()->create();
    $tenant = Tenant::factory()->create(['status' => TenantStatus::Active]);
    $token = $admin->createToken('admin');

    $this->withToken($token->plainTextToken)
        ->postJson(route('api.admin.tenants.suspend', $tenant))
        ->assertSuccessful()
        ->assertJsonPath('tenant.status', TenantStatus::Suspended->value);

    $this->assertDatabaseHas('tenants', [
        'id' => $tenant->id,
        'status' => TenantStatus::Suspended->value,
    ]);

    $this->withToken($token->plainTextToken)
        ->postJson(route('api.admin.tenants.activate', $tenant))
        ->assertSuccessful()
        ->assertJsonPath('tenant.status', TenantStatus::Active->value);
});

test('super admin can view tenant usage', function () {
    $admin = User::factory()->platformAdmin()->create();
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $owner->id,
    ]);
    $token = $admin->createToken('admin');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.admin.tenants.usage', $tenant))
        ->assertSuccessful()
        ->assertJsonPath('usage.users_count', 1)
        ->assertJsonStructure(['usage' => ['users_count', 'logins_last_30_days', 'plan_slug']]);
});

test('super admin can update tenant subscription plan', function () {
    $admin = User::factory()->platformAdmin()->create();
    $tenant = Tenant::factory()->create();
    $proPlan = Plan::query()->where('slug', 'pro')->first();
    $token = $admin->createToken('admin');

    $this->withToken($token->plainTextToken)
        ->patchJson(route('api.admin.tenants.subscription.update', $tenant), [
            'plan_id' => $proPlan->id,
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('subscriptions', [
        'tenant_id' => $tenant->id,
        'plan_id' => $proPlan->id,
    ]);
});

test('non admin cannot access admin tenant routes', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.admin.tenants.index'))
        ->assertForbidden();
});
