<?php

namespace App\Modules\Finance\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
