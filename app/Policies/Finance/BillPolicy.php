<?php

namespace App\Policies\Finance;

use App\Models\Finance\Bill;
use App\Models\Platform\Tenant;
use App\Models\User;

class BillPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Bill $bill): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($bill->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function update(User $user, Bill $bill): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($bill->tenant);
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($bill->tenant);
    }
}
