<?php

namespace App\Modules\Finance\Enums;

enum AccountType: string
{
    case Checking = 'checking';
    case Savings = 'savings';
    case Credit = 'credit';
    case Cash = 'cash';
}
