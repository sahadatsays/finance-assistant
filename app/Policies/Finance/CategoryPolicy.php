<?php

namespace App\Policies\Finance;

use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($tenant);
    }

    public function view(User $user, Category $category): bool
    {
        return $user->isPlatformAdmin() || $user->belongsToTenant($category->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($tenant);
    }

    public function update(User $user, Category $category): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($category->tenant);
    }

    public function delete(User $user, Category $category): bool
    {
        if ($category->is_system) {
            return false;
        }

        return $user->isPlatformAdmin() || $user->isOwnerOf($category->tenant);
    }

    public function archive(User $user, Category $category): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($category->tenant);
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->isPlatformAdmin() || $user->isOwnerOf($category->tenant);
    }
}
