<?php

namespace App\Modules\Finance\Enums;

enum GoalType: string
{
    case EmergencyFund = 'emergency_fund';
    case Travel = 'travel';
    case Education = 'education';
    case Purchase = 'purchase';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::EmergencyFund => 'Emergency Fund',
            self::Travel => 'Travel',
            self::Education => 'Education',
            self::Purchase => 'Purchase',
            self::Custom => 'Custom',
        };
    }

    public function defaultColor(): string
    {
        return match ($this) {
            self::EmergencyFund => '#ef4444',
            self::Travel => '#8b5cf6',
            self::Education => '#3b82f6',
            self::Purchase => '#06b6d4',
            self::Custom => '#10b981',
        };
    }
}
