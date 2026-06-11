<?php

namespace Database\Seeders;

use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;
use App\Modules\Tenant\Services\SubscriptionService;
use App\Modules\Tenant\Services\TenantService;
use App\Modules\Tenant\Services\TenantUserService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionUserSeeder extends Seeder
{
    /**
     * Default password for all seeded accounts.
     */
    public const string DEFAULT_PASSWORD = 'password';

    /**
     * Seed users for every platform and tenant role.
     *
     * Roles & permissions:
     * - Super Admin: full platform access (is_platform_admin)
     * - Tenant Owner: manage tenant settings, users, subscription (tenant-owner)
     * - Tenant User: read-only member access within tenant (tenant-user)
     * - Guest: authenticated user with no tenant membership
     */
    public function run(): void
    {
        $tenantService = app(TenantService::class);
        $tenantUserService = app(TenantUserService::class);
        $subscriptionService = app(SubscriptionService::class);

        $superAdmin = $this->createUser(
            email: 'admin@financeassistant.com',
            name: 'Super Admin',
            isPlatformAdmin: true,
        );

        $acmeOwner = $this->createUser(
            email: 'owner@acme.com',
            name: 'Acme Owner',
        );

        $startupOwner = $this->createUser(
            email: 'owner@startup.com',
            name: 'Startup Owner',
        );

        $suspendedOwner = $this->createUser(
            email: 'owner@suspended.com',
            name: 'Suspended Owner',
        );

        $acmeMember = $this->createUser(
            email: 'member@acme.com',
            name: 'Acme Member',
        );

        $startupMember = $this->createUser(
            email: 'member@startup.com',
            name: 'Startup Member',
        );

        $this->createUser(
            email: 'guest@example.com',
            name: 'Guest User',
        );

        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();
        $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();

        $acme = $this->seedTenant(
            tenantService: $tenantService,
            subscriptionService: $subscriptionService,
            createdBy: $superAdmin,
            name: 'Acme Corporation',
            slug: 'acme-corp',
            owner: $acmeOwner,
            planId: $proPlan->id,
            activate: true,
        );

        $this->attachMemberIfMissing($tenantUserService, $acme, $acmeMember);

        $this->seedTenant(
            tenantService: $tenantService,
            subscriptionService: $subscriptionService,
            createdBy: $superAdmin,
            name: 'Startup Inc',
            slug: 'startup-inc',
            owner: $startupOwner,
            planId: $proPlan->id,
            activate: false,
        );

        $this->attachMemberIfMissing(
            $tenantUserService,
            Tenant::query()->where('slug', 'startup-inc')->firstOrFail(),
            $startupMember,
        );

        $suspendedTenant = $this->seedTenant(
            tenantService: $tenantService,
            subscriptionService: $subscriptionService,
            createdBy: $superAdmin,
            name: 'Suspended LLC',
            slug: 'suspended-llc',
            owner: $suspendedOwner,
            planId: $freePlan->id,
            activate: true,
        );

        if ($suspendedTenant->status->isAccessible()) {
            $tenantService->suspend($suspendedTenant, $superAdmin);
        }
    }

    private function attachMemberIfMissing(
        TenantUserService $tenantUserService,
        Tenant $tenant,
        User $user,
    ): void {
        if ($tenant->tenantUsers()->where('user_id', $user->id)->exists()) {
            return;
        }

        $tenantUserService->attach($tenant, $user, TenantUserRole::User);
    }

    private function createUser(string $email, string $name, bool $isPlatformAdmin = false): User
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'is_platform_admin' => $isPlatformAdmin,
                'email_verified_at' => now(),
            ],
        );

        if ($user->profile === null) {
            $user->profile()->create([]);
        }

        return $user;
    }

    private function seedTenant(
        TenantService $tenantService,
        SubscriptionService $subscriptionService,
        User $createdBy,
        string $name,
        string $slug,
        User $owner,
        int $planId,
        bool $activate,
    ): Tenant {
        $tenant = Tenant::query()->where('slug', $slug)->first();

        if ($tenant === null) {
            $tenant = $tenantService->create([
                'name' => $name,
                'slug' => $slug,
                'owner_user_id' => $owner->id,
                'plan_id' => $planId,
            ], $createdBy);
        }

        if ($activate) {
            $tenant = $tenantService->activate($tenant, $createdBy);
            $subscription = $tenant->subscription()->first();

            if ($subscription !== null) {
                $subscriptionService->activate($subscription);
            }
        }

        return $tenant->fresh(['subscription.plan', 'tenantUsers.user']);
    }
}
