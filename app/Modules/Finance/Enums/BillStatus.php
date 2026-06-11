<?php

namespace App\Modules\Finance\Enums;

enum BillStatus: string
{
    case Upcoming = 'upcoming';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
        };
    }
}
