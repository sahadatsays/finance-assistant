<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Account;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\TransactionType;
use App\Services\Finance\TenantDashboardService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function __construct(
        private TenantDashboardService $dashboard,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(Tenant $tenant, ?Carbon $from = null, ?Carbon $to = null): array
    {
        [$from, $to] = $this->resolvePeriod($from, $to);
        $metrics = $this->periodMetrics($tenant, $from, $to);

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'income' => $metrics['income'],
            'expense' => $metrics['expense'],
            'net' => round($metrics['income'] - $metrics['expense'], 2),
            'savings' => $metrics['savings'],
            'net_worth' => $metrics['net_worth'],
            'budget_status' => $metrics['budget_status'],
        ];
    }

    /**
     * @return array{months: list<array{month: string, income: float, expense: float, net: float}>}
     */
    public function monthly(Tenant $tenant, int $months = 6): array
    {
        $months = max(1, min($months, 24));
        $since = Carbon::now()->subMonths($months - 1)->startOfMonth();
        $labels = $this->monthLabels($months);
        $totals = $this->monthlyTotals($tenant, $since);

        $rows = $labels->map(function (string $month) use ($totals): array {
            $income = round((float) ($totals[$month][TransactionType::Income->value] ?? 0), 2);
            $expense = round((float) ($totals[$month][TransactionType::Expense->value] ?? 0), 2);

            return [
                'month' => $month,
                'income' => $income,
                'expense' => $expense,
                'net' => round($income - $expense, 2),
            ];
        })->all();

        return ['months' => $rows];
    }

    /**
     * @return array{categories: list<array{category: string, color: string, amount: float, percentage: float}>}
     */
    public function category(Tenant $tenant, ?Carbon $from = null, ?Carbon $to = null): array
    {
        [$from, $to] = $this->resolvePeriod($from, $to);

        $rows = Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.tenant_id', $tenant->id)
            ->where('transactions.type', TransactionType::Expense)
            ->whereBetween('transactions.occurred_at', [$from, $to])
            ->selectRaw('categories.name as category, categories.color as color, SUM(transactions.amount) as amount')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('amount')
            ->get();

        $total = (float) $rows->sum('amount');

        $categories = $rows->map(fn ($row) => [
            'category' => $row->category,
            'color' => $row->color,
            'amount' => round((float) $row->amount, 2),
            'percentage' => $total > 0 ? round(((float) $row->amount / $total) * 100, 1) : 0.0,
        ])->all();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'total' => round($total, 2),
            'categories' => $categories,
        ];
    }

    /**
     * @return array{months: list<array{month: string, inflow: float, outflow: float, net: float}>}
     */
    public function cashflow(Tenant $tenant, int $months = 6): array
    {
        $monthly = $this->monthly($tenant, $months);

        return [
            'months' => collect($monthly['months'])->map(fn (array $row) => [
                'month' => $row['month'],
                'inflow' => $row['income'],
                'outflow' => $row['expense'],
                'net' => $row['net'],
            ])->all(),
        ];
    }

    /**
     * @return array{net_worth: float, accounts: list<array{id: int, name: string, type: string, balance: float}>}
     */
    public function netWorth(Tenant $tenant): array
    {
        $accounts = Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type->value,
                'balance' => round((float) $account->balance, 2),
            ])
            ->all();

        return [
            'net_worth' => round(collect($accounts)->sum('balance'), 2),
            'accounts' => $accounts,
        ];
    }

    /**
     * @return array{history: list<array{month: string, net_worth: float}>}
     */
    public function netWorthHistory(Tenant $tenant, int $months = 6): array
    {
        $months = max(1, min($months, 24));
        $current = $this->netWorth($tenant)['net_worth'];
        $labels = $this->monthLabels($months);

        $history = $labels->map(function (string $month) use ($tenant, $current): array {
            $endOfMonth = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            $changeSince = (float) Transaction::query()
                ->where('tenant_id', $tenant->id)
                ->where('occurred_at', '>', $endOfMonth)
                ->whereIn('type', [TransactionType::Income, TransactionType::Expense])
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END) as net_change")
                ->value('net_change');

            return [
                'month' => $month,
                'net_worth' => round($current - $changeSince, 2),
            ];
        })->all();

        return ['history' => $history];
    }

    /**
     * @return array{income: float, expense: float, savings: float, net_worth: float, budget_status: array<string, mixed>}
     */
    private function periodMetrics(Tenant $tenant, Carbon $from, Carbon $to): array
    {
        $dashboardMetrics = $this->dashboard->metrics($tenant);

        if ($from->isSameMonth(Carbon::now()) && $to->isSameMonth(Carbon::now())) {
            return [
                'income' => $dashboardMetrics['total_income'],
                'expense' => $dashboardMetrics['total_expense'],
                'savings' => $dashboardMetrics['total_savings'],
                'net_worth' => $dashboardMetrics['net_worth'],
                'budget_status' => $dashboardMetrics['budget_status'],
            ];
        }

        $totals = Transaction::query()
            ->selectRaw('type, SUM(amount) as total')
            ->where('tenant_id', $tenant->id)
            ->whereIn('type', [TransactionType::Income, TransactionType::Expense])
            ->whereBetween('occurred_at', [$from, $to])
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'income' => round((float) ($totals[TransactionType::Income->value] ?? 0), 2),
            'expense' => round((float) ($totals[TransactionType::Expense->value] ?? 0), 2),
            'savings' => $dashboardMetrics['total_savings'],
            'net_worth' => $dashboardMetrics['net_worth'],
            'budget_status' => $dashboardMetrics['budget_status'],
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(?Carbon $from, ?Carbon $to): array
    {
        $from ??= Carbon::now()->startOfMonth();
        $to ??= Carbon::now()->endOfMonth();

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    /**
     * @return Collection<int, string>
     */
    private function monthLabels(int $count): Collection
    {
        return collect(range($count - 1, 0))
            ->map(fn (int $offset) => Carbon::now()->subMonths($offset)->format('Y-m'));
    }

    /**
     * @return array<string, array<string, float>>
     */
    private function monthlyTotals(Tenant $tenant, Carbon $since): array
    {
        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', occurred_at)"
            : "DATE_FORMAT(occurred_at, '%Y-%m')";

        $rows = Transaction::query()
            ->selectRaw("{$monthExpression} as month, type, SUM(amount) as total")
            ->where('tenant_id', $tenant->id)
            ->whereIn('type', [TransactionType::Income, TransactionType::Expense])
            ->where('occurred_at', '>=', $since)
            ->groupBy('month', 'type')
            ->get();

        $totals = [];

        foreach ($rows as $row) {
            $type = $row->type instanceof TransactionType ? $row->type->value : (string) $row->type;
            $totals[$row->month][$type] = (float) $row->total;
        }

        return $totals;
    }
}
