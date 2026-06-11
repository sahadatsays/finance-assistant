<?php

namespace App\Policies\Finance;

use App\Models\Finance\Account;
use App\Models\Platform\Tenant;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Account $account): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($account->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }

    public function update(User $user, Account $account): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($account->tenant);
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($account->tenant);
    }
}
