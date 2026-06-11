<?php

namespace App\Modules\Finance\Services;

use App\Models\Platform\Tenant;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoalExportService
{
    public function __construct(
        private GoalAnalyticsService $analytics,
    ) {}

    public function exportCsv(Tenant $tenant): StreamedResponse
    {
        $report = $this->analytics->report($tenant);
        $filename = 'savings-goals-report-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Goal',
                'Type',
                'Target',
                'Current',
                'Progress %',
                'Status',
                'Target Date',
                'Required Monthly',
                'Avg Monthly',
                'Projected Completion',
                'Contribution Date',
                'Contribution Amount',
                'Contribution Notes',
            ]);

            foreach ($report as $goal) {
                if (count($goal['contributions']) === 0) {
                    fputcsv($handle, [
                        $goal['name'],
                        $goal['type_label'],
                        $goal['target_amount'],
                        $goal['current_amount'],
                        $goal['progress']['percentage'],
                        $goal['progress']['status'],
                        $goal['target_date'] ?? '',
                        $goal['forecast']['required_monthly'] ?? '',
                        $goal['forecast']['average_monthly'] ?? '',
                        $goal['forecast']['projected_completion'] ?? '',
                        '', '', '',
                    ]);

                    continue;
                }

                foreach ($goal['contributions'] as $index => $contribution) {
                    fputcsv($handle, [
                        $index === 0 ? $goal['name'] : '',
                        $index === 0 ? $goal['type_label'] : '',
                        $index === 0 ? $goal['target_amount'] : '',
                        $index === 0 ? $goal['current_amount'] : '',
                        $index === 0 ? $goal['progress']['percentage'] : '',
                        $index === 0 ? $goal['progress']['status'] : '',
                        $index === 0 ? ($goal['target_date'] ?? '') : '',
                        $index === 0 ? ($goal['forecast']['required_monthly'] ?? '') : '',
                        $index === 0 ? ($goal['forecast']['average_monthly'] ?? '') : '',
                        $index === 0 ? ($goal['forecast']['projected_completion'] ?? '') : '',
                        substr($contribution['contributed_at'], 0, 10),
                        $contribution['amount'],
                        $contribution['notes'] ?? '',
                    ]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
