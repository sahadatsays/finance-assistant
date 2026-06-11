<?php

namespace App\Modules\Finance\Enums;

enum BillRecurrence: string
{
    case Once = 'once';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Once => 'One-time',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }
}
