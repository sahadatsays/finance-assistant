<?php

namespace App\Modules\Finance\Enums;

enum CategoryKind: string
{
    case System = 'system';
    case Custom = 'custom';

    public static function fromSystemFlag(bool $isSystem): self
    {
        return $isSystem ? self::System : self::Custom;
    }
}
