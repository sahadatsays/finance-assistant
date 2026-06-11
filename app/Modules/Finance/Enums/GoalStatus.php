<?php

namespace App\Modules\Finance\Enums;

enum GoalStatus: string
{
    case OnTrack = 'on_track';
    case Behind = 'behind';
    case Completed = 'completed';

    public static function fromProgress(float $percentage, bool $isBehind): self
    {
        if ($percentage >= 100) {
            return self::Completed;
        }

        return $isBehind ? self::Behind : self::OnTrack;
    }
}
