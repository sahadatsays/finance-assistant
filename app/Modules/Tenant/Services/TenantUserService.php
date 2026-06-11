<?php

namespace App\Modules\Tenant\Services;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantUser;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TenantUserService
{
    public function attach(Tenant $tenant, User $user, TenantUserRole $role): TenantUser
    {
        if ($tenant->tenantUsers()->where('user_id', $user->id)->exists()) {
            throw new InvalidArgumentException('User is already a member of this tenant.');
        }

        $this->assertWithinPlanLimit($tenant);

        return TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * @param  array{email: string, name?: string, role?: TenantUserRole}  $data
     */
    public function invite(Tenant $tenant, array $data): TenantUser
    {
        $user = User::query()->firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? explode('@', $data['email'])[0],
                'password' => Str::password(16),
            ],
        );

        if ($user->profile === null) {
            $user->profile()->create([]);
        }

        if ($tenant->tenantUsers()->where('user_id', $user->id)->exists()) {
            throw new InvalidArgumentException('User is already a member of this tenant.');
        }

        $this->assertWithinPlanLimit($tenant);

        return TenantUser::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => $data['role'] ?? TenantUserRole::User,
            'invited_at' => now(),
            'joined_at' => null,
        ]);
    }

    public function updateRole(Tenant $tenant, User $user, TenantUserRole $role): TenantUser
    {
        $membership = $this->findMembership($tenant, $user);

        if ($membership->role === TenantUserRole::Owner && $role !== TenantUserRole::Owner) {
            $ownerCount = $tenant->tenantUsers()->where('role', TenantUserRole::Owner)->count();

            if ($ownerCount <= 1) {
                throw new InvalidArgumentException('Tenant must have at least one owner.');
            }
        }

        $membership->update(['role' => $role]);

        return $membership->fresh(['user']);
    }

    public function remove(Tenant $tenant, User $user): void
    {
        $membership = $this->findMembership($tenant, $user);

        if ($membership->role === TenantUserRole::Owner) {
            $ownerCount = $tenant->tenantUsers()->where('role', TenantUserRole::Owner)->count();

            if ($ownerCount <= 1) {
                throw new InvalidArgumentException('Cannot remove the only tenant owner.');
            }
        }

        $membership->delete();
    }

    /**
     * @return Collection<int, TenantUser>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return $tenant->tenantUsers()->with('user')->orderBy('role')->get();
    }

    private function findMembership(Tenant $tenant, User $user): TenantUser
    {
        $membership = $tenant->tenantUsers()->where('user_id', $user->id)->first();

        if ($membership === null) {
            throw new InvalidArgumentException('User is not a member of this tenant.');
        }

        return $membership;
    }

    private function assertWithinPlanLimit(Tenant $tenant): void
    {
        $tenant->loadMissing('subscription.plan');
        $maxUsers = $tenant->subscription?->plan?->max_users ?? 5;
        $currentCount = $tenant->tenantUsers()->count();

        if ($currentCount >= $maxUsers) {
            throw new InvalidArgumentException("Tenant has reached the maximum of {$maxUsers} users for its plan.");
        }
    }
}
