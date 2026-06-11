<?php

namespace App\Modules\Finance\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
    case Transfer = 'transfer';

    public function affectsBalance(): bool
    {
        return true;
    }

    public function requiresCategory(): bool
    {
        return $this !== self::Transfer;
    }
}
