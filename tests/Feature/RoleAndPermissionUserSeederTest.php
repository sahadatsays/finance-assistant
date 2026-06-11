<?php

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantStatus;
use App\Modules\Tenant\Enums\TenantUserRole;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RoleAndPermissionUserSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
});

test('role and permission user seeder creates all role accounts', function () {
    $this->seed(RoleAndPermissionUserSeeder::class);

    $superAdmin = User::query()->where('email', 'admin@financeassistant.com')->first();
    expect($superAdmin)->not->toBeNull()
        ->and($superAdmin->isPlatformAdmin())->toBeTrue();

    $acmeOwner = User::query()->where('email', 'owner@acme.com')->first();
    $acmeMember = User::query()->where('email', 'member@acme.com')->first();
    $startupOwner = User::query()->where('email', 'owner@startup.com')->first();
    $startupMember = User::query()->where('email', 'member@startup.com')->first();
    $suspendedOwner = User::query()->where('email', 'owner@suspended.com')->first();
    $guest = User::query()->where('email', 'guest@example.com')->first();

    expect($acmeOwner)->not->toBeNull()
        ->and($acmeMember)->not->toBeNull()
        ->and($startupOwner)->not->toBeNull()
        ->and($startupMember)->not->toBeNull()
        ->and($suspendedOwner)->not->toBeNull()
        ->and($guest)->not->toBeNull()
        ->and($guest->tenants)->toHaveCount(0);

    $acme = Tenant::query()->where('slug', 'acme-corp')->first();
    expect($acme)->not->toBeNull()
        ->and($acme->status)->toBe(TenantStatus::Active)
        ->and($acme->tenantUsers()->where('user_id', $acmeOwner->id)->first()->role)->toBe(TenantUserRole::Owner)
        ->and($acme->tenantUsers()->where('user_id', $acmeMember->id)->first()->role)->toBe(TenantUserRole::User);

    $startup = Tenant::query()->where('slug', 'startup-inc')->first();
    expect($startup)->not->toBeNull()
        ->and($startup->status)->toBe(TenantStatus::Trial)
        ->and($startup->tenantUsers()->where('user_id', $startupOwner->id)->first()->role)->toBe(TenantUserRole::Owner)
        ->and($startup->tenantUsers()->where('user_id', $startupMember->id)->first()->role)->toBe(TenantUserRole::User);

    $suspended = Tenant::query()->where('slug', 'suspended-llc')->first();
    expect($suspended)->not->toBeNull()
        ->and($suspended->status)->toBe(TenantStatus::Suspended)
        ->and($suspended->tenantUsers()->where('user_id', $suspendedOwner->id)->first()->role)->toBe(TenantUserRole::Owner);
});

test('role and permission user seeder is idempotent', function () {
    $this->seed(RoleAndPermissionUserSeeder::class);
    $this->seed(RoleAndPermissionUserSeeder::class);

    expect(User::query()->count())->toBe(7)
        ->and(Tenant::query()->count())->toBe(3);
});
