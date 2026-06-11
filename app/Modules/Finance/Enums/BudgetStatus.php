<?php

namespace App\Modules\Finance\Enums;

enum BudgetStatus: string
{
    case OnTrack = 'on_track';
    case Warning = 'warning';
    case OverBudget = 'over_budget';

    public static function fromPercentage(float $percentage): self
    {
        return match (true) {
            $percentage >= 100 => self::OverBudget,
            $percentage >= 80 => self::Warning,
            default => self::OnTrack,
        };
    }
}
