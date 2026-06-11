<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantUser;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;

beforeEach(function () {
    Plan::factory()->create(['slug' => 'free', 'max_users' => 10, 'is_active' => true]);
});

test('tenant owner can invite users', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $owner->id]);
    $token = $owner->createToken('test');

    $this->withToken($token->plainTextToken)
        ->postJson(route('api.tenants.users.store', $tenant), [
            'email' => 'member@example.com',
            'name' => 'New Member',
            'role' => TenantUserRole::User->value,
        ])
        ->assertCreated()
        ->assertJsonPath('member.role', TenantUserRole::User->value);

    $this->assertDatabaseHas('tenant_users', [
        'tenant_id' => $tenant->id,
        'role' => TenantUserRole::User->value,
    ]);
});

test('tenant owner can list tenant users', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $owner->id]);
    TenantUser::factory()->create(['tenant_id' => $tenant->id]);
    $token = $owner->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.tenants.users.index', $tenant))
        ->assertSuccessful()
        ->assertJsonCount(2, 'users');
});

test('tenant owner can remove a member', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $owner->id]);
    TenantUser::factory()->create(['tenant_id' => $tenant->id, 'user_id' => $member->id]);
    $token = $owner->createToken('test');

    $this->withToken($token->plainTextToken)
        ->deleteJson(route('api.tenants.users.destroy', [$tenant, $member]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('tenant_users', [
        'tenant_id' => $tenant->id,
        'user_id' => $member->id,
    ]);
});

test('suspended tenant is inaccessible to members', function () {
    $tenant = Tenant::factory()->suspended()->create();
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $owner->id]);
    $token = $owner->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.tenants.show', $tenant))
        ->assertForbidden();
});
