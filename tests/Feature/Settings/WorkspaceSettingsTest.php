<?php

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantUser;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;

beforeEach(function () {
    Plan::factory()->create(['slug' => 'free', 'max_users' => 10, 'is_active' => true]);
});

test('tenant owner can view workspace settings page', function () {
    $tenant = Tenant::factory()->create([
        'settings' => ['currency' => 'USD'],
    ]);
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->get(route('workspace.edit'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('settings/workspace')
            ->where('tenant.settings.currency', 'USD')
            ->has('currencies', 10));
});

test('tenant owner can update workspace currency', function () {
    $tenant = Tenant::factory()->create([
        'settings' => ['currency' => 'USD'],
    ]);
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->patch(route('workspace.update'), [
            'settings' => ['currency' => 'BDT'],
        ])
        ->assertRedirect(route('workspace.edit'));

    expect($tenant->fresh()->settings['currency'])->toBe('BDT');
});

test('tenant member cannot update workspace settings', function () {
    $tenant = Tenant::factory()->create([
        'settings' => ['currency' => 'USD'],
    ]);
    $member = User::factory()->create();
    TenantUser::factory()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $member->id,
        'role' => TenantUserRole::User,
    ]);

    $this->actingAs($member)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->patch(route('workspace.update'), [
            'settings' => ['currency' => 'EUR'],
        ])
        ->assertForbidden();
});

test('workspace settings rejects invalid currency', function () {
    $tenant = Tenant::factory()->create([
        'settings' => ['currency' => 'USD'],
    ]);
    $owner = User::factory()->create();
    TenantUser::factory()->owner()->create([
        'tenant_id' => $tenant->id,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->withSession(['current_tenant_id' => $tenant->id])
        ->patch(route('workspace.update'), [
            'settings' => ['currency' => 'XXX'],
        ])
        ->assertSessionHasErrors('settings.currency');
});
