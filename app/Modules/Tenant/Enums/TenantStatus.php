<?php

namespace App\Modules\Tenant\Enums;

enum TenantStatus: string
{
    case Pending = 'pending';
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function isAccessible(): bool
    {
        return match ($this) {
            self::Trial, self::Active => true,
            default => false,
        };
    }
}
