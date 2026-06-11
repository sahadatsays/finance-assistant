<?php

namespace App\Modules\Finance\Services;

use App\Models\Platform\Tenant;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetExportService
{
    public function __construct(
        private BudgetAnalyticsService $analytics,
    ) {}

    public function exportCsv(Tenant $tenant): StreamedResponse
    {
        $report = $this->analytics->report($tenant);
        $filename = 'budget-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Budget',
                'Period Type',
                'Period Start',
                'Period End',
                'Total Budgeted',
                'Total Spent',
                'Utilization %',
                'Status',
                'Category',
                'Category Budgeted',
                'Category Spent',
                'Category %',
            ]);

            foreach ($report as $budget) {
                if (count($budget['categories']) === 0) {
                    fputcsv($handle, [
                        $budget['name'],
                        $budget['period_type'],
                        $budget['period_start'],
                        $budget['period_end'],
                        $budget['amount'],
                        $budget['utilization']['spent'],
                        $budget['utilization']['percentage'],
                        $budget['utilization']['status'],
                        '', '', '', '',
                    ]);

                    continue;
                }

                foreach ($budget['categories'] as $index => $category) {
                    fputcsv($handle, [
                        $index === 0 ? $budget['name'] : '',
                        $index === 0 ? $budget['period_type'] : '',
                        $index === 0 ? $budget['period_start'] : '',
                        $index === 0 ? $budget['period_end'] : '',
                        $index === 0 ? $budget['amount'] : '',
                        $index === 0 ? $budget['utilization']['spent'] : '',
                        $index === 0 ? $budget['utilization']['percentage'] : '',
                        $index === 0 ? $budget['utilization']['status'] : '',
                        $category['category'],
                        $category['budgeted'],
                        $category['spent'],
                        $category['percentage'],
                    ]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
