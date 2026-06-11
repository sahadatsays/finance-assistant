<?php

namespace App\Policies\Finance;

use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($transaction->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($transaction->tenant);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($transaction->tenant);
    }

    public function export(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }
}
