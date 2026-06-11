<?php

namespace App\Services\Finance;

use App\Models\Finance\Account;
use App\Models\Finance\Goal;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\AccountType;
use App\Modules\Finance\Enums\BudgetPeriodType;
use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Services\BudgetAnalyticsService;
use App\Modules\Finance\Services\GoalAnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TenantDashboardService
{
    public function __construct(
        private BudgetAnalyticsService $budgetAnalytics,
        private GoalAnalyticsService $goalAnalytics,
    ) {}

    /**
     * @return array{
     *     total_income: float,
     *     total_expense: float,
     *     total_savings: float,
     *     budget_status: array{spent: float, budgeted: float, percentage: float, status: string},
     *     net_worth: float
     * }
     */
    public function metrics(Tenant $tenant): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyTotals = Transaction::query()
            ->selectRaw('type, SUM(amount) as total')
            ->where('tenant_id', $tenant->id)
            ->whereIn('type', [TransactionType::Income, TransactionType::Expense])
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->groupBy('type')
            ->pluck('total', 'type');

        $totalIncome = (float) ($monthlyTotals[TransactionType::Income->value] ?? 0);
        $totalExpense = (float) ($monthlyTotals[TransactionType::Expense->value] ?? 0);

        $accountTotals = Account::query()
            ->selectRaw('type, SUM(balance) as total')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->groupBy('type')
            ->pluck('total', 'type');

        $netWorth = (float) $accountTotals->sum();
        $totalSavings = (float) ($accountTotals[AccountType::Savings->value] ?? 0);

        $totalSavings += (float) Goal::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->sum('current_amount');

        $budgetStatus = $this->budgetStatus($tenant, $startOfMonth, $endOfMonth);

        return [
            'total_income' => round($totalIncome, 2),
            'total_expense' => round($totalExpense, 2),
            'total_savings' => round($totalSavings, 2),
            'budget_status' => $budgetStatus,
            'net_worth' => round($netWorth, 2),
        ];
    }

    /**
     * @return array{
     *     income_vs_expense: list<array{month: string, income: float, expense: float}>,
     *     category_breakdown: list<array{category: string, amount: float, color: string}>,
     *     monthly_trend: list<array{month: string, net: float}>
     * }
     */
    public function charts(Tenant $tenant): array
    {
        return [
            'income_vs_expense' => $this->incomeVsExpenseChart($tenant),
            'category_breakdown' => $this->categoryBreakdownChart($tenant),
            'monthly_trend' => $this->monthlyTrendChart($tenant),
        ];
    }

    /**
     * @return array{
     *     recent_transactions: list<array{id: int, notes: string|null, amount: float, type: string, category: string|null, occurred_at: string}>,
     *     budget_alerts: list<array{id: int, name: string, spent: float, budgeted: float, percentage: float, status: string}>,
     *     savings_goals: list<array{id: int, name: string, current_amount: float, target_amount: float, percentage: float, color: string, target_date: string|null}>
     * }
     */
    public function widgets(Tenant $tenant): array
    {
        return [
            'recent_transactions' => $this->recentTransactions($tenant),
            'budget_alerts' => $this->budgetAlerts($tenant),
            'savings_goals' => $this->savingsGoals($tenant),
        ];
    }

    /**
     * @return array{
     *     tenant: array{id: int, name: string, slug: string, currency: string},
     *     metrics: array{
     *         total_income: float,
     *         total_expense: float,
     *         total_savings: float,
     *         net_worth: float,
     *         budget_status: array{spent: float, budgeted: float, percentage: float, status: string},
     *         savings_goal_progress: array{
     *             summary: array{total_saved: float, total_target: float, percentage: float, active_count: int, completed_count: int},
     *             goals: list<array{id: int, name: string, current_amount: float, target_amount: float, percentage: float, color: string, target_date: string|null, status: string}>
     *         }
     *     },
     *     charts: array{
     *         income_vs_expense: list<array{month: string, income: float, expense: float}>,
     *         monthly_trend: list<array{month: string, net: float}>,
     *         category_breakdown: list<array{category: string, amount: float, color: string}>
     *     }
     * }
     */
    public function forApi(Tenant $tenant): array
    {
        if (! config('api.dashboard.cache_enabled', true)) {
            return $this->buildApiPayload($tenant);
        }

        $cacheKey = $this->apiCacheKey($tenant);
        $ttl = config('api.dashboard.cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, fn (): array => $this->buildApiPayload($tenant));
    }

    public function forgetApiCache(Tenant $tenant): void
    {
        Cache::forget($this->apiCacheKey($tenant));
    }

    /**
     * @return array{
     *     tenant: array{id: int, name: string, slug: string, currency: string},
     *     metrics: array<string, mixed>,
     *     charts: array<string, mixed>
     * }
     */
    private function buildApiPayload(Tenant $tenant): array
    {
        $metrics = $this->metrics($tenant);
        $goalDashboard = $this->goalAnalytics->dashboard($tenant);

        $metrics['savings_goal_progress'] = [
            'summary' => [
                ...$goalDashboard['summary'],
                'percentage' => $goalDashboard['summary']['total_target'] > 0
                    ? round(($goalDashboard['summary']['total_saved'] / $goalDashboard['summary']['total_target']) * 100, 1)
                    : 0.0,
            ],
            'goals' => collect($goalDashboard['goals'])
                ->map(fn (array $goal): array => [
                    'id' => $goal['id'],
                    'name' => $goal['name'],
                    'current_amount' => $goal['current_amount'],
                    'target_amount' => $goal['target_amount'],
                    'percentage' => $goal['progress']['percentage'],
                    'color' => $goal['color'],
                    'target_date' => $goal['target_date'],
                    'status' => $goal['progress']['status'],
                ])
                ->all(),
        ];

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'currency' => $tenant->settings['currency'] ?? 'USD',
            ],
            'metrics' => $metrics,
            'charts' => $this->charts($tenant),
        ];
    }

    private function apiCacheKey(Tenant $tenant): string
    {
        return sprintf('api.dashboard.%d.%s', $tenant->id, now()->format('Y-m'));
    }

    /**
     * @return array{spent: float, budgeted: float, percentage: float, status: string}
     */
    private function budgetStatus(Tenant $tenant, Carbon $start, Carbon $end): array
    {
        $budget = $this->budgetAnalytics->activeBudget($tenant, BudgetPeriodType::Monthly);

        if ($budget !== null) {
            return $this->budgetAnalytics->utilization($budget);
        }

        $spent = (float) Transaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        return [
            'spent' => round($spent, 2),
            'budgeted' => 0.0,
            'percentage' => 0.0,
            'status' => 'on_track',
        ];
    }

    /**
     * @return list<array{month: string, income: float, expense: float}>
     */
    private function incomeVsExpenseChart(Tenant $tenant): array
    {
        $months = $this->lastSixMonths();
        $since = Carbon::now()->subMonths(5)->startOfMonth();

        $monthlyTotals = $this->combinedMonthlyTotals($tenant, $since);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'income' => round((float) ($monthlyTotals[$month][TransactionType::Income->value] ?? 0), 2),
            'expense' => round((float) ($monthlyTotals[$month][TransactionType::Expense->value] ?? 0), 2),
        ])->all();
    }

    /**
     * @return list<array{category: string, amount: float, color: string}>
     */
    private function categoryBreakdownChart(Tenant $tenant): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.tenant_id', $tenant->id)
            ->where('transactions.type', TransactionType::Expense)
            ->whereBetween('transactions.occurred_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('categories.name as category, categories.color as color, SUM(transactions.amount) as amount')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'amount' => round((float) $row->amount, 2),
                'color' => $row->color,
            ])
            ->all();
    }

    /**
     * @return list<array{month: string, net: float}>
     */
    private function monthlyTrendChart(Tenant $tenant): array
    {
        $months = $this->lastSixMonths();
        $since = Carbon::now()->subMonths(5)->startOfMonth();

        $monthlyTotals = $this->combinedMonthlyTotals($tenant, $since);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'net' => round(
                (float) ($monthlyTotals[$month][TransactionType::Income->value] ?? 0)
                - (float) ($monthlyTotals[$month][TransactionType::Expense->value] ?? 0),
                2,
            ),
        ])->all();
    }

    /**
     * @return list<array{id: int, notes: string|null, amount: float, type: string, category: string|null, occurred_at: string}>
     */
    private function recentTransactions(Tenant $tenant): array
    {
        return Transaction::query()
            ->with('category')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'notes' => $transaction->notes,
                'amount' => round((float) $transaction->amount, 2),
                'type' => $transaction->type->value,
                'category' => $transaction->category?->name,
                'occurred_at' => $transaction->occurred_at->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, spent: float, budgeted: float, percentage: float, status: string}>
     */
    private function budgetAlerts(Tenant $tenant): array
    {
        return collect($this->budgetAnalytics->overspendingAlerts($tenant))
            ->map(fn (array $alert): array => [
                'id' => $alert['id'],
                'name' => isset($alert['category'])
                    ? "{$alert['name']} · {$alert['category']}"
                    : $alert['name'],
                'spent' => $alert['spent'],
                'budgeted' => $alert['budgeted'],
                'percentage' => $alert['percentage'],
                'status' => $alert['status'],
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, current_amount: float, target_amount: float, percentage: float, color: string, target_date: string|null}>
     */
    private function savingsGoals(Tenant $tenant): array
    {
        return collect($this->goalAnalytics->widgetGoals($tenant))
            ->map(fn (array $goal): array => [
                'id' => $goal['id'],
                'name' => $goal['name'],
                'current_amount' => $goal['current_amount'],
                'target_amount' => $goal['target_amount'],
                'percentage' => $goal['progress']['percentage'],
                'color' => $goal['color'],
                'target_date' => $goal['target_date'],
            ])
            ->all();
    }

    /**
     * @return array<string, array<string, float>>
     */
    private function combinedMonthlyTotals(Tenant $tenant, Carbon $since): array
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

    /**
     * @return Collection<int, string>
     */
    private function lastSixMonths(): Collection
    {
        return collect(range(5, 0))->map(
            fn (int $i) => Carbon::now()->subMonths($i)->format('Y-m'),
        );
    }
}
