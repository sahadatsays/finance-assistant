<?php

namespace App\Policies\Finance;

use App\Models\Platform\Tenant;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function export(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }
}
