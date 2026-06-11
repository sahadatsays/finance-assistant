<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Budget;
use App\Models\Finance\BudgetLine;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\BudgetPeriodType;
use App\Modules\Finance\Enums\BudgetStatus;
use App\Modules\Finance\Enums\TransactionType;

class BudgetAnalyticsService
{
    /**
     * @return array{
     *     spent: float,
     *     budgeted: float,
     *     remaining: float,
     *     percentage: float,
     *     status: string
     * }
     */
    public function utilization(Budget $budget): array
    {
        $spent = $this->spentForBudget($budget);
        $budgeted = (float) $budget->amount;
        $percentage = $budgeted > 0 ? round(($spent / $budgeted) * 100, 1) : 0;

        return [
            'spent' => round($spent, 2),
            'budgeted' => round($budgeted, 2),
            'remaining' => round(max($budgeted - $spent, 0), 2),
            'percentage' => $percentage,
            'status' => BudgetStatus::fromPercentage($percentage)->value,
        ];
    }

    /**
     * @return list<array{
     *     category_id: int,
     *     category: string,
     *     color: string,
     *     spent: float,
     *     budgeted: float,
     *     percentage: float,
     *     status: string
     * }>
     */
    public function categoryProgress(Budget $budget): array
    {
        $lines = $budget->lines()->with('category')->get();

        return $lines->map(function (BudgetLine $line) use ($budget): array {
            $spent = $this->spentForCategory($budget, $line->category_id);
            $budgeted = (float) $line->amount;
            $percentage = $budgeted > 0 ? round(($spent / $budgeted) * 100, 1) : 0;

            return [
                'category_id' => $line->category_id,
                'category' => $line->category->name,
                'color' => $line->category->color,
                'spent' => round($spent, 2),
                'budgeted' => round($budgeted, 2),
                'percentage' => $percentage,
                'status' => BudgetStatus::fromPercentage($percentage)->value,
            ];
        })->all();
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     category?: string,
     *     spent: float,
     *     budgeted: float,
     *     percentage: float,
     *     status: string
     * }>
     */
    public function overspendingAlerts(Tenant $tenant): array
    {
        $alerts = [];

        $activeBudgets = Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->get();

        foreach ($activeBudgets as $budget) {
            $utilization = $this->utilization($budget);

            if ($utilization['status'] !== BudgetStatus::OnTrack->value) {
                $alerts[] = [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'type' => 'overall',
                    'spent' => $utilization['spent'],
                    'budgeted' => $utilization['budgeted'],
                    'percentage' => $utilization['percentage'],
                    'status' => $utilization['status'],
                ];
            }

            foreach ($this->categoryProgress($budget) as $category) {
                if ($category['status'] === BudgetStatus::OnTrack->value) {
                    continue;
                }

                $alerts[] = [
                    'id' => $budget->id,
                    'name' => $budget->name,
                    'type' => 'category',
                    'category' => $category['category'],
                    'spent' => $category['spent'],
                    'budgeted' => $category['budgeted'],
                    'percentage' => $category['percentage'],
                    'status' => $category['status'],
                ];
            }
        }

        return $alerts;
    }

    /**
     * @return array{
     *     monthly: ?array{budget: array, utilization: array, categories: list},
     *     weekly: ?array{budget: array, utilization: array, categories: list},
     *     alerts: list,
     *     trend: list<array{period: string, spent: float, budgeted: float}>
     * }
     */
    public function dashboard(Tenant $tenant): array
    {
        $monthly = $this->activeBudgetSummary($tenant, BudgetPeriodType::Monthly);
        $weekly = $this->activeBudgetSummary($tenant, BudgetPeriodType::Weekly);

        return [
            'monthly' => $monthly,
            'weekly' => $weekly,
            'alerts' => $this->overspendingAlerts($tenant),
            'trend' => $this->utilizationTrend($tenant),
        ];
    }

    public function activeBudget(Tenant $tenant, BudgetPeriodType $type): ?Budget
    {
        return Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('period_type', $type)
            ->where('is_active', true)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->orderByDesc('period_start')
            ->first();
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     period_type: string,
     *     period_start: string,
     *     period_end: string,
     *     amount: float,
     *     utilization: array,
     *     categories: list
     * }>
     */
    public function report(Tenant $tenant): array
    {
        return Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByDesc('period_start')
            ->limit(12)
            ->get()
            ->map(fn (Budget $budget) => [
                'id' => $budget->id,
                'name' => $budget->name,
                'period_type' => $budget->period_type->value,
                'period_start' => $budget->period_start->toDateString(),
                'period_end' => $budget->period_end->toDateString(),
                'amount' => (float) $budget->amount,
                'utilization' => $this->utilization($budget),
                'categories' => $this->categoryProgress($budget),
            ])
            ->all();
    }

    /**
     * @return list<array{period: string, spent: float, budgeted: float, percentage: float}>
     */
    public function utilizationTrend(Tenant $tenant, int $limit = 6): array
    {
        return Budget::query()
            ->where('tenant_id', $tenant->id)
            ->where('period_type', BudgetPeriodType::Monthly)
            ->where('is_active', true)
            ->orderByDesc('period_start')
            ->limit($limit)
            ->get()
            ->sortBy('period_start')
            ->map(function (Budget $budget): array {
                $utilization = $this->utilization($budget);

                return [
                    'period' => $budget->period_start->format('Y-m'),
                    'spent' => $utilization['spent'],
                    'budgeted' => $utilization['budgeted'],
                    'percentage' => $utilization['percentage'],
                ];
            })
            ->values()
            ->all();
    }

    private function spentForBudget(Budget $budget): float
    {
        return (float) Transaction::query()
            ->where('tenant_id', $budget->tenant_id)
            ->where('type', TransactionType::Expense)
            ->whereBetween('occurred_at', [
                $budget->period_start->startOfDay(),
                $budget->period_end->endOfDay(),
            ])
            ->sum('amount');
    }

    private function spentForCategory(Budget $budget, int $categoryId): float
    {
        return (float) Transaction::query()
            ->where('tenant_id', $budget->tenant_id)
            ->where('type', TransactionType::Expense)
            ->where('category_id', $categoryId)
            ->whereBetween('occurred_at', [
                $budget->period_start->startOfDay(),
                $budget->period_end->endOfDay(),
            ])
            ->sum('amount');
    }

    /**
     * @return ?array{budget: array, utilization: array, categories: list}
     */
    private function activeBudgetSummary(Tenant $tenant, BudgetPeriodType $type): ?array
    {
        $budget = $this->activeBudget($tenant, $type);

        if ($budget === null) {
            return null;
        }

        return [
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'period_type' => $budget->period_type->value,
                'period_start' => $budget->period_start->toDateString(),
                'period_end' => $budget->period_end->toDateString(),
                'amount' => (float) $budget->amount,
            ],
            'utilization' => $this->utilization($budget),
            'categories' => $this->categoryProgress($budget),
        ];
    }
}
