<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Account;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\AccountType;
use App\Services\Platform\ActivityLogService;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class AccountService
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @return Collection<int, Account>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->withCount('transactions')
            ->orderBy('name')
            ->get();
    }

    public function findForTenant(Tenant $tenant, int $accountId): ?Account
    {
        return Account::query()
            ->where('tenant_id', $tenant->id)
            ->find($accountId);
    }

    /**
     * @param  array{name: string, type: string, balance?: float|string, currency?: string}  $data
     */
    public function create(Tenant $tenant, array $data, User $user): Account
    {
        $account = Account::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'type' => AccountType::from($data['type']),
            'balance' => $data['balance'] ?? 0,
            'currency' => $data['currency'] ?? $tenant->currency ?? 'USD',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Account \"{$account->name}\" was created.",
            logName: 'finance',
            subject: $account,
            causer: $user,
            tenant: $tenant,
        );

        return $account;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Account $account, array $data, User $user): Account
    {
        if (isset($data['balance'])) {
            throw new InvalidArgumentException('Balance cannot be updated directly. Use transactions.');
        }

        $account->update([
            'name' => $data['name'] ?? $account->name,
            'type' => isset($data['type']) ? AccountType::from($data['type']) : $account->type,
            'currency' => $data['currency'] ?? $account->currency,
        ]);

        $this->activityLog->log(
            "Account \"{$account->name}\" was updated.",
            logName: 'finance',
            subject: $account,
            causer: $user,
            tenant: $account->tenant,
        );

        return $account->fresh();
    }

    public function delete(Account $account, User $user): void
    {
        if ($account->transactions()->exists()) {
            throw new InvalidArgumentException('Cannot delete an account with transactions.');
        }

        $account->update(['is_active' => false]);

        $this->activityLog->log(
            "Account \"{$account->name}\" was archived.",
            logName: 'finance',
            subject: $account,
            causer: $user,
            tenant: $account->tenant,
        );
    }
}
