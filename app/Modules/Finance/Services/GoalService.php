<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Goal;
use App\Models\Finance\GoalContribution;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\GoalType;
use App\Services\Platform\ActivityLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GoalService
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @return Collection<int, Goal>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return Goal::query()
            ->with(['contributions' => fn ($q) => $q->orderByDesc('contributed_at')->limit(5)])
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('target_date')
            ->get();
    }

    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     target_amount: float|string,
     *     target_date?: string|null,
     *     color?: string|null,
     *     initial_contribution?: float|string|null
     * }  $data
     */
    public function create(Tenant $tenant, array $data, User $user): Goal
    {
        return DB::transaction(function () use ($tenant, $data, $user): Goal {
            $type = GoalType::from($data['type']);
            $color = $data['color'] ?? $type->defaultColor();

            $goal = Goal::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'type' => $type,
                'target_amount' => $data['target_amount'],
                'current_amount' => 0,
                'target_date' => $data['target_date'] ?? null,
                'color' => $color,
                'is_active' => true,
                'created_by' => $user->id,
            ]);

            if (isset($data['initial_contribution']) && (float) $data['initial_contribution'] > 0) {
                $this->addContribution($goal, [
                    'amount' => $data['initial_contribution'],
                    'notes' => 'Initial contribution',
                    'contributed_at' => now()->toDateTimeString(),
                ], $user);
            }

            $this->activityLog->log(
                "Savings goal \"{$goal->name}\" was created.",
                logName: 'finance',
                subject: $goal,
                causer: $user,
                tenant: $tenant,
                properties: ['type' => $type->value],
            );

            return $goal->fresh(['contributions']);
        });
    }

    /**
     * @param  array{
     *     name?: string,
     *     type?: string,
     *     target_amount?: float|string,
     *     target_date?: string|null,
     *     color?: string|null
     * }  $data
     */
    public function update(Goal $goal, array $data, User $user): Goal
    {
        return DB::transaction(function () use ($goal, $data, $user): Goal {
            $updates = array_filter([
                'name' => $data['name'] ?? null,
                'target_amount' => $data['target_amount'] ?? null,
                'color' => $data['color'] ?? null,
            ], fn ($value) => $value !== null);

            if (array_key_exists('target_date', $data)) {
                $updates['target_date'] = $data['target_date'];
            }

            if (isset($data['type'])) {
                $updates['type'] = GoalType::from($data['type']);
            }

            $goal->update($updates);

            $this->activityLog->log(
                "Savings goal \"{$goal->name}\" was updated.",
                logName: 'finance',
                subject: $goal,
                causer: $user,
                tenant: $goal->tenant,
            );

            return $goal->fresh(['contributions']);
        });
    }

    public function delete(Goal $goal, User $user): void
    {
        DB::transaction(function () use ($goal, $user): void {
            $name = $goal->name;
            $tenant = $goal->tenant;

            $goal->contributions()->delete();
            $goal->delete();

            $this->activityLog->log(
                "Savings goal \"{$name}\" was deleted.",
                logName: 'finance',
                causer: $user,
                tenant: $tenant,
            );
        });
    }

    /**
     * @param  array{
     *     amount: float|string,
     *     notes?: string|null,
     *     contributed_at?: string|null
     * }  $data
     */
    public function addContribution(Goal $goal, array $data, User $user): GoalContribution
    {
        return DB::transaction(function () use ($goal, $data, $user): GoalContribution {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new InvalidArgumentException('Contribution amount must be greater than zero.');
            }

            $contribution = GoalContribution::query()->create([
                'goal_id' => $goal->id,
                'amount' => $amount,
                'notes' => $data['notes'] ?? null,
                'contributed_at' => isset($data['contributed_at'])
                    ? Carbon::parse($data['contributed_at'])
                    : now(),
                'created_by' => $user->id,
            ]);

            $goal->increment('current_amount', $amount);

            $this->activityLog->log(
                "Contribution of {$amount} added to savings goal \"{$goal->name}\".",
                logName: 'finance',
                subject: $goal,
                causer: $user,
                tenant: $goal->tenant,
                properties: ['amount' => $amount],
            );

            return $contribution;
        });
    }

    public function deleteContribution(GoalContribution $contribution, User $user): void
    {
        DB::transaction(function () use ($contribution, $user): void {
            $goal = $contribution->goal;
            $amount = (float) $contribution->amount;

            $contribution->delete();
            $goal->decrement('current_amount', $amount);

            $this->activityLog->log(
                "Contribution of {$amount} removed from savings goal \"{$goal->name}\".",
                logName: 'finance',
                subject: $goal,
                causer: $user,
                tenant: $goal->tenant,
            );
        });
    }
}
