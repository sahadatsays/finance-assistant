<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantUser;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;

beforeEach(function () {
    Plan::factory()->create(['slug' => 'free', 'max_users' => 10, 'is_active' => true]);
});

test('tenant owner can update tenant settings', function () {
    $tenant = Tenant::factory()->create();
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $owner->id]);
    $token = $owner->createToken('test');

    $this->withToken($token->plainTextToken)
        ->patchJson(route('api.tenants.settings.update', $tenant), [
            'name' => 'Updated Tenant Name',
            'settings' => [
                'timezone' => 'America/New_York',
                'currency' => 'USD',
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('tenant.name', 'Updated Tenant Name')
        ->assertJsonPath('tenant.settings.timezone', 'America/New_York');
});

test('tenant user cannot update tenant settings', function () {
    $tenant = Tenant::factory()->create();
    $member = User::factory()->create();
    TenantUser::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $member->id,
        'role' => TenantUserRole::User,
    ]);
    $token = $member->createToken('test');

    $this->withToken($token->plainTextToken)
        ->patchJson(route('api.tenants.settings.update', $tenant), [
            'name' => 'Hacked Name',
        ])
        ->assertForbidden();
});

test('user can list their tenants', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    TenantUser::factory()->owner()->create(['tenant_id' => $tenant->id, 'user_id' => $user->id]);
    $token = $user->createToken('test');

    $this->withToken($token->plainTextToken)
        ->getJson(route('api.tenants.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'tenants');
});
