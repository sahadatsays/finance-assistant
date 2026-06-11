<?php

namespace App\Modules\Finance\Enums;

enum BudgetPeriodType: string
{
    case Monthly = 'monthly';
    case Weekly = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Weekly => 'Weekly',
        };
    }
}
