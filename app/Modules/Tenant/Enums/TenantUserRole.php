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
}
