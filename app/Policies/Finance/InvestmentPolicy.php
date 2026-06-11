<?php

namespace App\Policies\Finance;

use App\Models\Finance\Investment;
use App\Models\Platform\Tenant;
use App\Models\User;

class InvestmentPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function update(User $user, Investment $investment): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($investment->tenant);
    }

    public function delete(User $user, Investment $investment): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($investment->tenant);
    }
}
