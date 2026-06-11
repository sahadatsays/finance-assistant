<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportService
{
    public function __construct(
        private TransactionService $transactions,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(Tenant $tenant, array $filters = []): StreamedResponse
    {
        $rows = $this->transactions->exportCollection($tenant, $filters);

        $filename = 'transactions-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Type',
                'Category',
                'Account',
                'Transfer To',
                'Amount',
                'Notes',
                'Tags',
            ]);

            foreach ($rows as $transaction) {
                /** @var Transaction $transaction */
                fputcsv($handle, [
                    $transaction->occurred_at->toDateString(),
                    $transaction->type->value,
                    $transaction->category?->name ?? '',
                    $transaction->account?->name ?? '',
                    $transaction->transferAccount?->name ?? '',
                    $transaction->amount,
                    $transaction->notes ?? '',
                    $transaction->tags->pluck('name')->join(', '),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
