<?php

namespace App\Modules\Finance\Enums;

enum RecurrenceFrequency: string
{
    case EveryMinute = 'every_minute';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::EveryMinute => 'Every minute',
            self::Daily => 'Every day',
            self::Weekly => 'Every week',
            self::Biweekly => 'Every 2 weeks',
            self::Monthly => 'Every month',
        };
    }
}
