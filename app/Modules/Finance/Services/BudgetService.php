<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Budget;
use App\Models\Finance\BudgetLine;
use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\BudgetPeriodType;
use App\Modules\Finance\Enums\CategoryType;
use App\Services\Platform\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BudgetService
{
    public function __construct(
        private ActivityLogService $activityLog,
        private BudgetAnalyticsService $analytics,
    ) {}

    /**
     * @return Collection<int, Budget>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderByDesc('period_start')
            ->get();
    }

    /**
     * @param  array{period_type?: string, sort?: string, direction?: string}  $filters
     * @return LengthAwarePaginator<int, Budget>
     */
    public function paginateForTenant(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true);

        if (! empty($filters['period_type'])) {
            $query->where('period_type', $filters['period_type']);
        }

        $allowedSorts = ['period_start', 'period_end', 'amount', 'name', 'created_at'];
        $sort = in_array($filters['sort'] ?? '', $allowedSorts, true)
            ? $filters['sort']
            : 'period_start';
        $direction = in_array(strtolower($filters['direction'] ?? ''), ['asc', 'desc'], true)
            ? strtolower($filters['direction'])
            : 'desc';

        return $query
            ->orderBy($sort, $direction)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findForTenant(Tenant $tenant, int $budgetId): ?Budget
    {
        return Budget::query()
            ->with('lines.category')
            ->where('tenant_id', $tenant->id)
            ->find($budgetId);
    }

    /**
     * @param  array{
     *     name: string,
     *     period_type: string,
     *     period_start?: string,
     *     amount?: float|string,
     *     lines: list<array{category_id: int, amount: float|string}>
     * }  $data
     */
    public function create(Tenant $tenant, array $data, User $user): Budget
    {
        return DB::transaction(function () use ($tenant, $data, $user): Budget {
            $periodType = BudgetPeriodType::from($data['period_type']);
            $period = $this->resolvePeriod($periodType, $data['period_start'] ?? null);

            $lines = $this->normalizeLines($data['lines']);
            $this->validateLines($tenant, $lines);

            $totalAmount = isset($data['amount'])
                ? (float) $data['amount']
                : collect($lines)->sum(fn ($line) => (float) $line['amount']);

            $budget = Budget::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'period_type' => $periodType,
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'amount' => $totalAmount,
                'is_active' => true,
                'created_by' => $user->id,
            ]);

            $this->syncLines($budget, $lines);

            $this->activityLog->log(
                "Budget \"{$budget->name}\" was created.",
                logName: 'finance',
                subject: $budget,
                causer: $user,
                tenant: $tenant,
                properties: ['period_type' => $periodType->value],
            );

            return $budget->load('lines.category');
        });
    }

    /**
     * @param  array{
     *     name?: string,
     *     amount?: float|string,
     *     lines?: list<array{category_id: int, amount: float|string}>
     * }  $data
     */
    public function update(Budget $budget, array $data, User $user): Budget
    {
        return DB::transaction(function () use ($budget, $data, $user): Budget {
            if (isset($data['lines'])) {
                $lines = $this->normalizeLines($data['lines']);
                $this->validateLines($budget->tenant, $lines);
                $this->syncLines($budget, $lines);

                if (! isset($data['amount'])) {
                    $data['amount'] = collect($lines)->sum(fn ($line) => (float) $line['amount']);
                }
            }

            $budget->update(array_filter([
                'name' => $data['name'] ?? null,
                'amount' => $data['amount'] ?? null,
            ], fn ($value) => $value !== null));

            $this->activityLog->log(
                "Budget \"{$budget->name}\" was updated.",
                logName: 'finance',
                subject: $budget,
                causer: $user,
                tenant: $budget->tenant,
            );

            return $budget->fresh(['lines.category']);
        });
    }

    public function delete(Budget $budget, User $user): void
    {
        DB::transaction(function () use ($budget, $user): void {
            $name = $budget->name;
            $tenant = $budget->tenant;

            $budget->lines()->delete();
            $budget->delete();

            $this->activityLog->log(
                "Budget \"{$name}\" was deleted.",
                logName: 'finance',
                causer: $user,
                tenant: $tenant,
            );
        });
    }

    /**
     * @param  list<array{category_id: int, amount: float|string}>  $lines
     * @return list<array{category_id: int, amount: float}>
     */
    private function normalizeLines(array $lines): array
    {
        return collect($lines)
            ->groupBy(fn (array $line): int => (int) $line['category_id'])
            ->map(fn (Collection $group, int|string $categoryId): array => [
                'category_id' => (int) $categoryId,
                'amount' => $group->sum(fn (array $line): float => (float) $line['amount']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{category_id: int, amount: float|string}>  $lines
     */
    private function syncLines(Budget $budget, array $lines): void
    {
        $budget->lines()->delete();

        foreach ($lines as $line) {
            BudgetLine::query()->create([
                'budget_id' => $budget->id,
                'category_id' => $line['category_id'],
                'amount' => $line['amount'],
            ]);
        }
    }

    /**
     * @param  list<array{category_id: int, amount: float|string}>  $lines
     */
    private function validateLines(Tenant $tenant, array $lines): void
    {
        if (count($lines) === 0) {
            throw new InvalidArgumentException('At least one category budget line is required.');
        }

        foreach ($lines as $line) {
            $valid = Category::query()
                ->where('tenant_id', $tenant->id)
                ->where('id', $line['category_id'])
                ->where('type', CategoryType::Expense)
                ->where('is_active', true)
                ->exists();

            if (! $valid) {
                throw new InvalidArgumentException('Invalid expense category selected.');
            }

            if ((float) $line['amount'] <= 0) {
                throw new InvalidArgumentException('Category budget amounts must be greater than zero.');
            }
        }
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    private function resolvePeriod(BudgetPeriodType $type, ?string $startDate): array
    {
        $start = $startDate !== null
            ? Carbon::parse($startDate)->startOfDay()
            : now()->startOfDay();

        return match ($type) {
            BudgetPeriodType::Monthly => [
                'start' => $start->copy()->startOfMonth(),
                'end' => $start->copy()->endOfMonth(),
            ],
            BudgetPeriodType::Weekly => [
                'start' => $start->copy()->startOfWeek(),
                'end' => $start->copy()->endOfWeek(),
            ],
        };
    }
}
