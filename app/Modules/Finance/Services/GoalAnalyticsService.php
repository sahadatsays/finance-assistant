<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Goal;
use App\Models\Finance\GoalContribution;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\GoalStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class GoalAnalyticsService
{
    /**
     * @return array{
     *     current: float,
     *     target: float,
     *     remaining: float,
     *     percentage: float,
     *     status: string
     * }
     */
    public function progress(Goal $goal): array
    {
        $current = (float) $goal->current_amount;
        $target = (float) $goal->target_amount;
        $percentage = $target > 0 ? round(min(($current / $target) * 100, 100), 1) : 0;
        $forecast = $this->forecast($goal);

        return [
            'current' => round($current, 2),
            'target' => round($target, 2),
            'remaining' => round(max($target - $current, 0), 2),
            'percentage' => $percentage,
            'status' => GoalStatus::fromProgress($percentage, $forecast['is_behind'])->value,
        ];
    }

    /**
     * @return array{
     *     remaining: float,
     *     days_remaining: ?int,
     *     required_monthly: ?float,
     *     average_monthly: ?float,
     *     projected_completion: ?string,
     *     is_behind: bool
     * }
     */
    public function forecast(Goal $goal): array
    {
        $current = (float) $goal->current_amount;
        $target = (float) $goal->target_amount;
        $remaining = max($target - $current, 0);

        if ($remaining <= 0) {
            return [
                'remaining' => 0.0,
                'days_remaining' => null,
                'required_monthly' => null,
                'average_monthly' => null,
                'projected_completion' => null,
                'is_behind' => false,
            ];
        }

        $daysRemaining = $goal->target_date !== null
            ? max((int) now()->startOfDay()->diffInDays($goal->target_date->endOfDay(), false), 0)
            : null;

        $monthsRemaining = $daysRemaining !== null
            ? max($daysRemaining / 30, 0.5)
            : null;

        $requiredMonthly = $monthsRemaining !== null
            ? round($remaining / $monthsRemaining, 2)
            : null;

        $averageMonthly = $this->averageMonthlyContribution($goal);

        $projectedCompletion = null;
        if ($averageMonthly !== null && $averageMonthly > 0) {
            $monthsToComplete = $remaining / $averageMonthly;
            $projectedCompletion = now()->addDays((int) round($monthsToComplete * 30))->toDateString();
        }

        $isBehind = false;
        if ($goal->target_date !== null && $projectedCompletion !== null) {
            $isBehind = Carbon::parse($projectedCompletion)->gt($goal->target_date);
        } elseif ($goal->target_date !== null && $daysRemaining !== null && $daysRemaining > 0) {
            $expectedProgress = 100 - (($daysRemaining / max(
                (int) $goal->target_date->diffInDays($goal->created_at ?? now()->subYear()),
                1,
            )) * 100);
            $actualProgress = $target > 0 ? ($current / $target) * 100 : 0;
            $isBehind = $actualProgress < max($expectedProgress - 10, 0);
        }

        return [
            'remaining' => round($remaining, 2),
            'days_remaining' => $daysRemaining,
            'required_monthly' => $requiredMonthly,
            'average_monthly' => $averageMonthly,
            'projected_completion' => $projectedCompletion,
            'is_behind' => $isBehind,
        ];
    }

    /**
     * @return list<array{month: string, amount: float}>
     */
    public function contributionTrend(Goal $goal, int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $contributions = $goal->contributions()
            ->where('contributed_at', '>=', $since)
            ->get();

        $monthsList = collect(range($months - 1, 0))->map(
            fn (int $i) => now()->subMonths($i)->format('Y-m'),
        );

        $grouped = $contributions->groupBy(
            fn (GoalContribution $c) => $c->contributed_at->format('Y-m'),
        );

        return $monthsList->map(fn (string $month) => [
            'month' => $month,
            'amount' => round((float) ($grouped->get($month)?->sum('amount') ?? 0), 2),
        ])->all();
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     type_label: string,
     *     target_amount: float,
     *     current_amount: float,
     *     target_date: ?string,
     *     color: string,
     *     progress: array,
     *     forecast: array
     * }>
     */
    public function widgetGoals(Tenant $tenant): array
    {
        return Goal::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('target_date')
            ->get()
            ->map(fn (Goal $goal) => $this->goalSummary($goal))
            ->all();
    }

    /**
     * @return array{
     *     summary: array{total_saved: float, total_target: float, active_count: int, completed_count: int},
     *     by_type: list<array{type: string, label: string, count: int, saved: float, target: float}>,
     *     trend: list<array{month: string, amount: float}>,
     *     goals: list
     * }
     */
    public function dashboard(Tenant $tenant): array
    {
        $goals = Goal::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $totalSaved = round($goals->sum(fn (Goal $g) => (float) $g->current_amount), 2);
        $totalTarget = round($goals->sum(fn (Goal $g) => (float) $g->target_amount), 2);
        $completedCount = $goals->filter(fn (Goal $g) => $g->isCompleted())->count();

        $byType = $goals->groupBy(fn (Goal $g) => $g->type->value)->map(function (Collection $group, string $type) {
            $goalType = $group->first()->type;

            return [
                'type' => $type,
                'label' => $goalType->label(),
                'count' => $group->count(),
                'saved' => round($group->sum(fn (Goal $g) => (float) $g->current_amount), 2),
                'target' => round($group->sum(fn (Goal $g) => (float) $g->target_amount), 2),
            ];
        })->values()->all();

        return [
            'summary' => [
                'total_saved' => $totalSaved,
                'total_target' => $totalTarget,
                'active_count' => $goals->count(),
                'completed_count' => $completedCount,
            ],
            'by_type' => $byType,
            'trend' => $this->tenantContributionTrend($tenant),
            'goals' => $goals->map(fn (Goal $g) => $this->goalSummary($g))->all(),
        ];
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     type_label: string,
     *     target_amount: float,
     *     current_amount: float,
     *     target_date: ?string,
     *     color: string,
     *     progress: array,
     *     forecast: array
     * }>
     */
    public function report(Tenant $tenant): array
    {
        return Goal::query()
            ->with('contributions')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('target_date')
            ->get()
            ->map(fn (Goal $goal) => [
                ...$this->goalSummary($goal),
                'contributions' => $goal->contributions
                    ->sortByDesc('contributed_at')
                    ->map(fn (GoalContribution $c) => [
                        'id' => $c->id,
                        'amount' => (float) $c->amount,
                        'notes' => $c->notes,
                        'contributed_at' => $c->contributed_at->toIso8601String(),
                    ])
                    ->values()
                    ->all(),
            ])
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     type: string,
     *     type_label: string,
     *     target_amount: float,
     *     current_amount: float,
     *     target_date: ?string,
     *     color: string,
     *     progress: array,
     *     forecast: array
     * }
     */
    public function goalSummary(Goal $goal): array
    {
        return [
            'id' => $goal->id,
            'name' => $goal->name,
            'type' => $goal->type->value,
            'type_label' => $goal->type->label(),
            'target_amount' => (float) $goal->target_amount,
            'current_amount' => (float) $goal->current_amount,
            'target_date' => $goal->target_date?->toDateString(),
            'color' => $goal->color,
            'progress' => $this->progress($goal),
            'forecast' => $this->forecast($goal),
        ];
    }

    /**
     * @return list<array{month: string, amount: float}>
     */
    private function tenantContributionTrend(Tenant $tenant, int $months = 6): array
    {
        $since = now()->subMonths($months - 1)->startOfMonth();

        $contributions = GoalContribution::query()
            ->whereHas('goal', fn ($q) => $q->where('tenant_id', $tenant->id)->where('is_active', true))
            ->where('contributed_at', '>=', $since)
            ->get();

        $monthsList = collect(range($months - 1, 0))->map(
            fn (int $i) => now()->subMonths($i)->format('Y-m'),
        );

        $grouped = $contributions->groupBy(
            fn (GoalContribution $c) => $c->contributed_at->format('Y-m'),
        );

        return $monthsList->map(fn (string $month) => [
            'month' => $month,
            'amount' => round((float) ($grouped->get($month)?->sum('amount') ?? 0), 2),
        ])->all();
    }

    private function averageMonthlyContribution(Goal $goal): ?float
    {
        $contributions = $goal->contributions()
            ->where('contributed_at', '>=', now()->subMonths(6))
            ->get();

        if ($contributions->isEmpty()) {
            return null;
        }

        $total = (float) $contributions->sum('amount');
        $firstDate = $contributions->min('contributed_at');
        $months = max($firstDate->diffInMonths(now()) ?: 1, 1);

        return round($total / $months, 2);
    }
}
