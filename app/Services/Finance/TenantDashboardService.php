<?php

namespace App\Services\Finance;

use App\Models\Finance\Account;
use App\Models\Finance\Budget;
use App\Models\Finance\Goal;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\AccountType;
use App\Modules\Finance\Enums\TransactionType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantDashboardService
{
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

        $totalIncome = (float) Transaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', TransactionType::Income)
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalExpense = (float) Transaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $totalSavings = (float) Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', AccountType::Savings)
            ->where('is_active', true)
            ->sum('balance');

        $totalSavings += (float) Goal::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->sum('current_amount');

        $netWorth = (float) Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->sum('balance');

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
     * @return array{spent: float, budgeted: float, percentage: float, status: string}
     */
    private function budgetStatus(Tenant $tenant, Carbon $start, Carbon $end): array
    {
        $budget = Budget::query()
            ->where('tenant_id', $tenant->id)
            ->where('period_start', '<=', $end)
            ->where('period_end', '>=', $start)
            ->orderByDesc('period_start')
            ->first();

        $budgeted = (float) ($budget?->amount ?? 0);

        $spent = (float) Transaction::query()
            ->where('tenant_id', $tenant->id)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        $percentage = $budgeted > 0 ? round(($spent / $budgeted) * 100, 1) : 0;

        return [
            'spent' => round($spent, 2),
            'budgeted' => round($budgeted, 2),
            'percentage' => $percentage,
            'status' => $this->budgetAlertStatus($percentage),
        ];
    }

    /**
     * @return list<array{month: string, income: float, expense: float}>
     */
    private function incomeVsExpenseChart(Tenant $tenant): array
    {
        $months = $this->lastSixMonths();
        $since = Carbon::now()->subMonths(5)->startOfMonth();

        $income = $this->monthlyTotals($tenant, TransactionType::Income, $since);
        $expense = $this->monthlyTotals($tenant, TransactionType::Expense, $since);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'income' => round((float) ($income[$month] ?? 0), 2),
            'expense' => round((float) ($expense[$month] ?? 0), 2),
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

        $income = $this->monthlyTotals($tenant, TransactionType::Income, $since);
        $expense = $this->monthlyTotals($tenant, TransactionType::Expense, $since);

        return $months->map(fn (string $month) => [
            'month' => $month,
            'net' => round((float) ($income[$month] ?? 0) - (float) ($expense[$month] ?? 0), 2),
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
        $now = Carbon::now();

        return Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('period_start', '<=', $now)
            ->where('period_end', '>=', $now)
            ->get()
            ->map(function (Budget $budget) use ($tenant): ?array {
                $spent = (float) Transaction::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('type', TransactionType::Expense)
                    ->whereBetween('occurred_at', [$budget->period_start, $budget->period_end])
                    ->sum('amount');

                $budgeted = (float) $budget->amount;
                $percentage = $budgeted > 0 ? round(($spent / $budgeted) * 100, 1) : 0;
                $status = $this->budgetAlertStatus($percentage);

                if ($status === 'on_track') {
                    return null;
                }

                return [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'spent' => round($spent, 2),
                    'budgeted' => round($budgeted, 2),
                    'percentage' => $percentage,
                    'status' => $status,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, current_amount: float, target_amount: float, percentage: float, color: string, target_date: string|null}>
     */
    private function savingsGoals(Tenant $tenant): array
    {
        return Goal::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('target_date')
            ->get()
            ->map(function (Goal $goal): array {
                $target = (float) $goal->target_amount;
                $current = (float) $goal->current_amount;
                $percentage = $target > 0 ? round(($current / $target) * 100, 1) : 0;

                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'current_amount' => round($current, 2),
                    'target_amount' => round($target, 2),
                    'percentage' => min($percentage, 100),
                    'color' => $goal->color,
                    'target_date' => $goal->target_date?->toDateString(),
                ];
            })
            ->all();
    }

    /**
     * @return array<string, float|int|string>
     */
    private function monthlyTotals(Tenant $tenant, TransactionType $type, Carbon $since): array
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return Transaction::query()
                ->selectRaw("strftime('%Y-%m', occurred_at) as month, SUM(amount) as total")
                ->where('tenant_id', $tenant->id)
                ->where('type', $type)
                ->where('occurred_at', '>=', $since)
                ->groupBy('month')
                ->pluck('total', 'month')
                ->all();
        }

        return Transaction::query()
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m') as month, SUM(amount) as total")
            ->where('tenant_id', $tenant->id)
            ->where('type', $type)
            ->where('occurred_at', '>=', $since)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();
    }

    private function budgetAlertStatus(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'over_budget',
            $percentage >= 80 => 'warning',
            default => 'on_track',
        };
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
