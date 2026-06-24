<?php

namespace App\Console\Commands\Finance;

use App\Modules\Finance\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class ProcessRecurringTransactionsCommand extends Command
{
    protected $signature = 'finance:process-recurring-transactions';

    protected $description = 'Create transactions for due recurring income and expense schedules';

    public function handle(RecurringTransactionService $recurringTransactions): int
    {
        $processed = $recurringTransactions->processDue();

        $this->info("Processed {$processed} recurring transaction(s).");

        return self::SUCCESS;
    }
}
