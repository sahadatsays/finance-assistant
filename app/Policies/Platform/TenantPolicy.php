<?php

namespace App\Policies\Platform;

use App\Models\Platform\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function create(User $user): bool
    {
        return $user->isPlatformAdmin();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }

    public function suspend(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin();
    }

    public function activate(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin();
    }

    public function viewUsage(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin();
    }

    public function manageSubscription(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin();
    }

    public function manageUsers(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }
}
