<?php

namespace App\Policies\Finance;

use App\Models\Finance\RecurringTransaction;
use App\Models\Platform\Tenant;
use App\Models\User;

class RecurringTransactionPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($recurringTransaction->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($recurringTransaction->tenant);
    }

    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($recurringTransaction->tenant);
    }
}
