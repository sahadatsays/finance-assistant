<?php

namespace App\Modules\Finance\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Checking',
            self::Savings => 'Savings',
            self::Credit => 'Credit',
            self::Cash => 'Cash',
        };
    }
}
