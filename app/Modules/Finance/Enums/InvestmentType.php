<?php

namespace App\Modules\Finance\Enums;

enum InvestmentType: string
{
    case Stock = 'stock';
    case Etf = 'etf';
    case Crypto = 'crypto';
    case Bond = 'bond';
    case Other = 'other';
}
