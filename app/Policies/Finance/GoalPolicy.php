<?php

namespace App\Policies\Finance;

use App\Models\Finance\Goal;
use App\Models\Platform\Tenant;
use App\Models\User;

class GoalPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Goal $goal): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($goal->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }

    public function update(User $user, Goal $goal): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($goal->tenant);
    }

    public function delete(User $user, Goal $goal): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($goal->tenant);
    }

    public function contribute(User $user, Goal $goal): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($goal->tenant);
    }

    public function export(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }
}
