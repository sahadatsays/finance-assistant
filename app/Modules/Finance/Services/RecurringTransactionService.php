<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\RecurringTransaction;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\RecurrenceFrequency;
use App\Modules\Finance\Enums\TransactionType;
use App\Services\Platform\ActivityLogService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecurringTransactionService
{
    public function __construct(
        private TransactionService $transactions,
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @return Collection<int, RecurringTransaction>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return RecurringTransaction::query()
            ->with(['account', 'category'])
            ->where('tenant_id', $tenant->id)
            ->orderBy('next_run_at')
            ->get();
    }

    public function findForTenant(Tenant $tenant, int $recurringTransactionId): ?RecurringTransaction
    {
        return RecurringTransaction::query()
            ->with(['account', 'category'])
            ->where('tenant_id', $tenant->id)
            ->find($recurringTransactionId);
    }

    /**
     * @param  array{
     *     name: string,
     *     type: string,
     *     amount: float|string,
     *     account_id: int,
     *     category_id: int,
     *     frequency: string,
     *     run_time?: string,
     *     start_date: string,
     *     notes?: string|null
     * }  $data
     */
    public function create(Tenant $tenant, array $data, User $user): RecurringTransaction
    {
        $type = TransactionType::from($data['type']);
        $frequency = RecurrenceFrequency::from($data['frequency']);
        $runTime = $this->normalizeRunTime($data['run_time'] ?? '09:00');
        $startDate = Carbon::parse($data['start_date'])->startOfDay();

        $this->validateRuleData($tenant, $type, $data);

        $nextRunAt = $this->resolveInitialNextRunAt($frequency, $startDate, $runTime);

        $rule = RecurringTransaction::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'type' => $type,
            'amount' => $data['amount'],
            'account_id' => $data['account_id'],
            'category_id' => $data['category_id'],
            'frequency' => $frequency,
            'run_time' => $runTime,
            'start_date' => $startDate->toDateString(),
            'next_run_at' => $nextRunAt,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Recurring transaction \"{$rule->name}\" was created.",
            logName: 'finance',
            subject: $rule,
            causer: $user,
            tenant: $tenant,
        );

        return $rule->load(['account', 'category']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(RecurringTransaction $rule, array $data, User $user): RecurringTransaction
    {
        $type = isset($data['type'])
            ? TransactionType::from($data['type'])
            : $rule->type;

        $merged = [
            'name' => $data['name'] ?? $rule->name,
            'type' => $type,
            'amount' => $data['amount'] ?? $rule->amount,
            'account_id' => $data['account_id'] ?? $rule->account_id,
            'category_id' => $data['category_id'] ?? $rule->category_id,
            'frequency' => isset($data['frequency'])
                ? RecurrenceFrequency::from($data['frequency'])
                : $rule->frequency,
            'run_time' => isset($data['run_time'])
                ? $this->normalizeRunTime($data['run_time'])
                : $this->normalizeRunTime((string) $rule->run_time),
            'start_date' => isset($data['start_date'])
                ? Carbon::parse($data['start_date'])->toDateString()
                : $rule->start_date->toDateString(),
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $rule->notes,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $rule->is_active,
        ];

        $this->validateRuleData($rule->tenant, $type, array_merge($merged, [
            'type' => $type->value,
            'frequency' => $merged['frequency']->value,
        ]));

        $scheduleChanged = $merged['frequency'] !== $rule->frequency
            || $merged['run_time'] !== $this->normalizeRunTime((string) $rule->run_time)
            || $merged['start_date'] !== $rule->start_date->toDateString();

        $nextRunAt = $scheduleChanged
            ? $this->resolveInitialNextRunAt(
                $merged['frequency'],
                Carbon::parse($merged['start_date']),
                $merged['run_time'],
            )
            : $rule->next_run_at;

        $rule->update([
            ...$merged,
            'next_run_at' => $nextRunAt,
            'updated_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Recurring transaction \"{$rule->name}\" was updated.",
            logName: 'finance',
            subject: $rule,
            causer: $user,
            tenant: $rule->tenant,
        );

        return $rule->fresh(['account', 'category']);
    }

    public function delete(RecurringTransaction $rule, User $user): void
    {
        $rule->update(['is_active' => false]);

        $this->activityLog->log(
            "Recurring transaction \"{$rule->name}\" was paused.",
            logName: 'finance',
            subject: $rule,
            causer: $user,
            tenant: $rule->tenant,
        );
    }

    public function resume(RecurringTransaction $rule, User $user): RecurringTransaction
    {
        if ($rule->is_active) {
            return $rule->load(['account', 'category']);
        }

        $runTime = $this->normalizeRunTime((string) $rule->run_time);
        $nextRunAt = $this->resolveInitialNextRunAt(
            $rule->frequency,
            $rule->start_date,
            $runTime,
        );

        $rule->update([
            'is_active' => true,
            'next_run_at' => $nextRunAt,
            'updated_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Recurring transaction \"{$rule->name}\" was resumed.",
            logName: 'finance',
            subject: $rule,
            causer: $user,
            tenant: $rule->tenant,
        );

        return $rule->fresh(['account', 'category']);
    }

    public function processDue(?CarbonInterface $asOf = null): int
    {
        $asOf ??= now();
        $processed = 0;

        RecurringTransaction::query()
            ->with(['tenant', 'creator'])
            ->where('is_active', true)
            ->where('next_run_at', '<=', $asOf)
            ->orderBy('id')
            ->chunkById(50, function (Collection $rules) use ($asOf, &$processed): void {
                foreach ($rules as $rule) {
                    $processed += $this->processRule($rule, $asOf);
                }
            });

        return $processed;
    }

    public function resolveInitialNextRunAt(
        RecurrenceFrequency $frequency,
        CarbonInterface $startDate,
        string $runTime,
        ?CarbonInterface $asOf = null,
    ): CarbonInterface {
        $asOf ??= now();
        $anchor = $this->combineDateAndTime($startDate, $runTime);

        if ($anchor->greaterThan($asOf)) {
            return $anchor;
        }

        return $this->calculateNextRunAtAfter($frequency, $startDate, $runTime, $asOf);
    }

    private function processRule(RecurringTransaction $rule, CarbonInterface $asOf): int
    {
        $processed = 0;
        $maxOccurrences = $rule->frequency === RecurrenceFrequency::EveryMinute ? 1440 : 1;

        while ($processed < $maxOccurrences && $this->processRuleOnce($rule, $asOf)) {
            $processed++;
            $rule->refresh();
        }

        return $processed;
    }

    private function processRuleOnce(RecurringTransaction $rule, CarbonInterface $asOf): bool
    {
        return (bool) DB::transaction(function () use ($rule, $asOf): bool {
            /** @var RecurringTransaction|null $locked */
            $locked = RecurringTransaction::query()
                ->whereKey($rule->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! $locked->is_active || $locked->next_run_at->greaterThan($asOf)) {
                return false;
            }

            if ($this->transactionExistsForOccurrence($locked, $locked->next_run_at)) {
                $locked->update([
                    'next_run_at' => $this->calculateNextRunAtAfter(
                        $locked->frequency,
                        $locked->start_date,
                        $this->normalizeRunTime((string) $locked->run_time),
                        $locked->next_run_at,
                    ),
                ]);

                return false;
            }

            $user = $this->resolveActor($locked);
            $occurredAt = $locked->next_run_at->copy();

            $this->transactions->create(
                $locked->tenant,
                [
                    'type' => $locked->type->value,
                    'account_id' => $locked->account_id,
                    'category_id' => $locked->category_id,
                    'amount' => $locked->amount,
                    'occurred_at' => $occurredAt->toDateTimeString(),
                    'notes' => $this->buildTransactionNotes($locked),
                    'recurring_transaction_id' => $locked->id,
                ],
                $user,
            );

            $locked->update([
                'last_run_at' => $occurredAt,
                'next_run_at' => $this->calculateNextRunAtAfter(
                    $locked->frequency,
                    $locked->start_date,
                    $this->normalizeRunTime((string) $locked->run_time),
                    $occurredAt,
                ),
            ]);

            return true;
        });
    }

    private function transactionExistsForOccurrence(RecurringTransaction $rule, CarbonInterface $occurredAt): bool
    {
        return Transaction::query()
            ->where('recurring_transaction_id', $rule->id)
            ->where('occurred_at', $occurredAt)
            ->exists();
    }

    private function buildTransactionNotes(RecurringTransaction $rule): string
    {
        $prefix = "Auto: {$rule->name}";

        if ($rule->notes === null || $rule->notes === '') {
            return $prefix;
        }

        return "{$prefix} — {$rule->notes}";
    }

    private function resolveActor(RecurringTransaction $rule): User
    {
        if ($rule->creator !== null) {
            return $rule->creator;
        }

        $owner = $rule->tenant->users()
            ->wherePivot('role', 'tenant-owner')
            ->first();

        if ($owner !== null) {
            return $owner;
        }

        throw new InvalidArgumentException("No user available to post recurring transaction \"{$rule->name}\".");
    }

    private function calculateNextRunAtAfter(
        RecurrenceFrequency $frequency,
        CarbonInterface $startDate,
        string $runTime,
        CarbonInterface $after,
    ): CarbonInterface {
        return match ($frequency) {
            RecurrenceFrequency::EveryMinute => $this->nextEveryMinute($after),
            RecurrenceFrequency::Daily => $this->nextDaily($runTime, $after),
            RecurrenceFrequency::Weekly => $this->nextIntervalFromAnchor($startDate, $runTime, $after, 7),
            RecurrenceFrequency::Biweekly => $this->nextIntervalFromAnchor($startDate, $runTime, $after, 14),
            RecurrenceFrequency::Monthly => $this->nextMonthlyFromAnchor($startDate, $runTime, $after),
        };
    }

    private function nextEveryMinute(CarbonInterface $after): CarbonInterface
    {
        return Carbon::parse($after)->addMinute()->startOfMinute();
    }

    private function nextDaily(string $runTime, CarbonInterface $after): CarbonInterface
    {
        $candidate = $this->combineDateAndTime($after->copy()->startOfDay(), $runTime);

        if ($candidate->lessThanOrEqualTo($after)) {
            $candidate->addDay();
        }

        return $candidate;
    }

    private function nextIntervalFromAnchor(
        CarbonInterface $startDate,
        string $runTime,
        CarbonInterface $after,
        int $days,
    ): CarbonInterface {
        $anchor = $this->combineDateAndTime($startDate, $runTime);

        if ($anchor->greaterThan($after)) {
            return $anchor;
        }

        $candidate = $anchor->copy();

        while ($candidate->lessThanOrEqualTo($after)) {
            $candidate->addDays($days);
        }

        return $candidate;
    }

    private function nextMonthlyFromAnchor(
        CarbonInterface $startDate,
        string $runTime,
        CarbonInterface $after,
    ): CarbonInterface {
        $anchor = $this->combineDateAndTime($startDate, $runTime);
        $day = $startDate->day;

        if ($anchor->greaterThan($after)) {
            return $anchor;
        }

        $candidate = $anchor->copy();

        while ($candidate->lessThanOrEqualTo($after)) {
            $candidate->addMonthNoOverflow();
            $candidate->day(min($day, $candidate->daysInMonth));
            $candidate->setTimeFromTimeString($runTime);
        }

        return $candidate;
    }

    private function combineDateAndTime(CarbonInterface $date, string $runTime): CarbonInterface
    {
        $time = $this->normalizeRunTime($runTime);
        [$hour, $minute, $second] = array_map('intval', explode(':', $time));

        return Carbon::parse($date)->setTime($hour, $minute, $second);
    }

    private function normalizeRunTime(string $runTime): string
    {
        $parts = explode(':', $runTime);

        return sprintf(
            '%02d:%02d:%02d',
            (int) ($parts[0] ?? 0),
            (int) ($parts[1] ?? 0),
            (int) ($parts[2] ?? 0),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateRuleData(Tenant $tenant, TransactionType $type, array $data): void
    {
        if (! in_array($type, [TransactionType::Income, TransactionType::Expense], true)) {
            throw new InvalidArgumentException('Only income and expense schedules are supported.');
        }

        if ((float) $data['amount'] <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        $accountExists = Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $data['account_id'])
            ->where('is_active', true)
            ->exists();

        if (! $accountExists) {
            throw new InvalidArgumentException('Invalid account selected.');
        }

        $expectedCategoryType = $type === TransactionType::Income
            ? CategoryType::Income
            : CategoryType::Expense;

        $categoryExists = Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $data['category_id'])
            ->where('type', $expectedCategoryType)
            ->where('is_active', true)
            ->exists();

        if (! $categoryExists) {
            throw new InvalidArgumentException('Invalid category for this transaction type.');
        }
    }
}
