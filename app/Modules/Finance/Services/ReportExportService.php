<?php

namespace App\Modules\Finance\Services;

use App\Models\Platform\Tenant;
use App\Support\Reports\SimplePdfDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(
        private ReportService $reports,
    ) {}

    /**
     * @param  array{report: string, format: string, from?: string, to?: string, months?: int}  $options
     */
    public function export(Tenant $tenant, array $options): JsonResponse|StreamedResponse
    {
        $report = $options['report'];
        $format = strtolower($options['format']);
        $data = $this->resolveReportData($tenant, $report, $options);
        $filename = "{$report}-".now()->format('Y-m-d-His');

        return match ($format) {
            'json' => $this->exportJson($data, $filename),
            'csv' => $this->exportCsv($report, $data, $filename),
            'pdf' => $this->exportPdf($report, $data, $filename),
            default => throw new InvalidArgumentException('Unsupported export format.'),
        };
    }

    /**
     * @param  array{report: string, from?: string, to?: string, months?: int}  $options
     * @return array<string, mixed>
     */
    private function resolveReportData(Tenant $tenant, string $report, array $options): array
    {
        $from = isset($options['from']) ? Carbon::parse($options['from']) : null;
        $to = isset($options['to']) ? Carbon::parse($options['to']) : null;
        $months = (int) ($options['months'] ?? 6);

        return match ($report) {
            'summary' => $this->reports->summary($tenant, $from, $to),
            'monthly' => $this->reports->monthly($tenant, $months),
            'category' => $this->reports->category($tenant, $from, $to),
            'cashflow' => $this->reports->cashflow($tenant, $months),
            'net-worth' => array_merge(
                $this->reports->netWorth($tenant),
                $this->reports->netWorthHistory($tenant, $months),
            ),
            default => throw new InvalidArgumentException('Unsupported report type.'),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function exportJson(array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(
            fn () => print (json_encode($data, JSON_PRETTY_PRINT) ?: '{}'),
            "{$filename}.json",
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function exportCsv(string $report, array $data, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($report, $data): void {
            $handle = fopen('php://output', 'w');

            match ($report) {
                'summary' => $this->writeSummaryCsv($handle, $data),
                'monthly', 'cashflow' => $this->writeMonthlyCsv($handle, $data['months'] ?? []),
                'category' => $this->writeCategoryCsv($handle, $data),
                'net-worth' => $this->writeNetWorthCsv($handle, $data),
                default => fputcsv($handle, ['error', 'Unsupported report']),
            };

            fclose($handle);
        }, "{$filename}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function exportPdf(string $report, array $data, string $filename): StreamedResponse
    {
        $lines = ["Report: {$report}", 'Generated: '.now()->toDateTimeString()];

        if ($report === 'summary') {
            $lines[] = "Income: {$data['income']}";
            $lines[] = "Expense: {$data['expense']}";
            $lines[] = "Net: {$data['net']}";
            $lines[] = "Net Worth: {$data['net_worth']}";
        } elseif (in_array($report, ['monthly', 'cashflow'], true)) {
            foreach ($data['months'] ?? [] as $row) {
                $lines[] = "{$row['month']}: net {$row['net']}";
            }
        } elseif ($report === 'category') {
            foreach ($data['categories'] ?? [] as $row) {
                $lines[] = "{$row['category']}: {$row['amount']}";
            }
        } elseif ($report === 'net-worth') {
            $lines[] = 'Current Net Worth: '.($data['net_worth'] ?? 0);
            foreach ($data['history'] ?? [] as $row) {
                $lines[] = "{$row['month']}: {$row['net_worth']}";
            }
        }

        $pdf = SimplePdfDocument::fromLines('Finance Report', $lines);

        return response()->streamDownload(
            fn () => print ($pdf),
            "{$filename}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $data
     */
    private function writeSummaryCsv($handle, array $data): void
    {
        fputcsv($handle, ['metric', 'value']);
        fputcsv($handle, ['income', $data['income']]);
        fputcsv($handle, ['expense', $data['expense']]);
        fputcsv($handle, ['net', $data['net']]);
        fputcsv($handle, ['net_worth', $data['net_worth']]);
    }

    /**
     * @param  resource  $handle
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeMonthlyCsv($handle, array $rows): void
    {
        fputcsv($handle, ['month', 'income', 'expense', 'net']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['month'],
                $row['income'] ?? $row['inflow'] ?? 0,
                $row['expense'] ?? $row['outflow'] ?? 0,
                $row['net'],
            ]);
        }
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $data
     */
    private function writeCategoryCsv($handle, array $data): void
    {
        fputcsv($handle, ['category', 'amount', 'percentage']);

        foreach ($data['categories'] ?? [] as $row) {
            fputcsv($handle, [$row['category'], $row['amount'], $row['percentage']]);
        }
    }

    /**
     * @param  resource  $handle
     * @param  array<string, mixed>  $data
     */
    private function writeNetWorthCsv($handle, array $data): void
    {
        fputcsv($handle, ['account', 'type', 'balance']);

        foreach ($data['accounts'] ?? [] as $row) {
            fputcsv($handle, [$row['name'], $row['type'], $row['balance']]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['month', 'net_worth']);

        foreach ($data['history'] ?? [] as $row) {
            fputcsv($handle, [$row['month'], $row['net_worth']]);
        }
    }
}
