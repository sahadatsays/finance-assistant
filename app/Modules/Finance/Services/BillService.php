<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Bill;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\BillRecurrence;
use App\Modules\Finance\Enums\BillStatus;
use App\Services\Platform\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BillService
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Bill>
     */
    public function paginateForTenant(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bill::query()
            ->with('category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Bill>
     */
    public function upcomingForTenant(Tenant $tenant, int $days = 30): Collection
    {
        $this->refreshStatuses($tenant);

        return Bill::query()
            ->with('category')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('status', '!=', BillStatus::Paid)
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->orderBy('due_date')
            ->get();
    }

    public function findForTenant(Tenant $tenant, int $billId): ?Bill
    {
        return Bill::query()
            ->with('category')
            ->where('tenant_id', $tenant->id)
            ->find($billId);
    }

    /**
     * @param  array{name: string, amount: float|string, due_date: string, recurrence?: string, category_id?: int|null, notes?: string|null}  $data
     */
    public function create(Tenant $tenant, array $data, User $user): Bill
    {
        $bill = Bill::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'recurrence' => BillRecurrence::from($data['recurrence'] ?? BillRecurrence::Monthly->value),
            'status' => $this->resolveStatus(Carbon::parse($data['due_date'])),
            'category_id' => $data['category_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Bill \"{$bill->name}\" was created.",
            logName: 'finance',
            subject: $bill,
            causer: $user,
            tenant: $tenant,
        );

        return $bill->load('category');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Bill $bill, array $data, User $user): Bill
    {
        $bill->update([
            'name' => $data['name'] ?? $bill->name,
            'amount' => $data['amount'] ?? $bill->amount,
            'due_date' => $data['due_date'] ?? $bill->due_date,
            'recurrence' => isset($data['recurrence'])
                ? BillRecurrence::from($data['recurrence'])
                : $bill->recurrence,
            'category_id' => array_key_exists('category_id', $data) ? $data['category_id'] : $bill->category_id,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $bill->notes,
        ]);

        if (isset($data['due_date']) && $bill->status !== BillStatus::Paid) {
            $bill->update(['status' => $this->resolveStatus($bill->due_date)]);
        }

        $this->activityLog->log(
            "Bill \"{$bill->name}\" was updated.",
            logName: 'finance',
            subject: $bill,
            causer: $user,
            tenant: $bill->tenant,
        );

        return $bill->fresh(['category']);
    }

    public function delete(Bill $bill, User $user): void
    {
        $bill->update(['is_active' => false]);

        $this->activityLog->log(
            "Bill \"{$bill->name}\" was deleted.",
            logName: 'finance',
            subject: $bill,
            causer: $user,
            tenant: $bill->tenant,
        );
    }

    public function markPaid(Bill $bill, User $user): Bill
    {
        return DB::transaction(function () use ($bill, $user): Bill {
            $bill->update([
                'status' => BillStatus::Paid,
                'paid_at' => now(),
            ]);

            if ($bill->recurrence !== BillRecurrence::Once) {
                $nextDue = match ($bill->recurrence) {
                    BillRecurrence::Weekly => $bill->due_date->copy()->addWeek(),
                    BillRecurrence::Monthly => $bill->due_date->copy()->addMonth(),
                    BillRecurrence::Yearly => $bill->due_date->copy()->addYear(),
                    default => null,
                };

                if ($nextDue !== null) {
                    Bill::query()->create([
                        'tenant_id' => $bill->tenant_id,
                        'name' => $bill->name,
                        'amount' => $bill->amount,
                        'due_date' => $nextDue,
                        'recurrence' => $bill->recurrence,
                        'status' => $this->resolveStatus($nextDue),
                        'category_id' => $bill->category_id,
                        'notes' => $bill->notes,
                        'is_active' => true,
                        'created_by' => $user->id,
                    ]);
                }
            }

            $this->activityLog->log(
                "Bill \"{$bill->name}\" was marked as paid.",
                logName: 'finance',
                subject: $bill,
                causer: $user,
                tenant: $bill->tenant,
            );

            return $bill->fresh(['category']);
        });
    }

    private function refreshStatuses(Tenant $tenant): void
    {
        Bill::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('status', '!=', BillStatus::Paid)
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => BillStatus::Overdue]);
    }

    private function resolveStatus(CarbonInterface $dueDate): BillStatus
    {
        if ($dueDate->lt(now()->startOfDay())) {
            return BillStatus::Overdue;
        }

        return BillStatus::Upcoming;
    }
}
