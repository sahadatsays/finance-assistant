<?php

namespace App\Policies\Finance;

use App\Models\Finance\Budget;
use App\Models\Platform\Tenant;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($budget->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($budget->tenant);
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($budget->tenant);
    }

    public function export(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }
}
