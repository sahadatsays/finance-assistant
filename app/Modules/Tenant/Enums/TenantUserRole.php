<?php

namespace App\Modules\Tenant\Enums;

enum TenantUserRole: string
{
    case Owner = 'tenant-owner';
    case User = 'tenant-user';

    public function canManageTenant(): bool
    {
        return $this === self::Owner;
    }

    public function canManageUsers(): bool
    {
        return $this === self::Owner;
    }

    public function canManageCategories(): bool
    {
        return $this === self::Owner;
    }

    public function canManageTransactions(): bool
    {
        return true;
    }

    public function canManageBudgets(): bool
    {
        return $this === self::Owner;
    }

    public function canManageGoals(): bool
    {
        return $this === self::Owner;
    }
}
