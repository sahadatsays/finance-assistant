<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Investment;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\InvestmentType;
use Illuminate\Support\Collection;

class InvestmentService
{
    public function listForTenant(Tenant $tenant): Collection
    {
        return Investment::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findForTenant(Tenant $tenant, int $id): ?Investment
    {
        return Investment::query()->where('tenant_id', $tenant->id)->find($id);
    }

    public function create(Tenant $tenant, array $data, User $user): Investment
    {
        return Investment::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'symbol' => $data['symbol'] ?? null,
            'type' => InvestmentType::from($data['type']),
            'quantity' => $data['quantity'],
            'cost_basis' => $data['cost_basis'],
            'current_price' => $data['current_price'],
            'purchased_at' => $data['purchased_at'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
    }

    public function update(Investment $investment, array $data): Investment
    {
        $investment->update([
            'name' => $data['name'] ?? $investment->name,
            'symbol' => array_key_exists('symbol', $data) ? $data['symbol'] : $investment->symbol,
            'type' => isset($data['type']) ? InvestmentType::from($data['type']) : $investment->type,
            'quantity' => $data['quantity'] ?? $investment->quantity,
            'cost_basis' => $data['cost_basis'] ?? $investment->cost_basis,
            'current_price' => $data['current_price'] ?? $investment->current_price,
            'purchased_at' => array_key_exists('purchased_at', $data) ? $data['purchased_at'] : $investment->purchased_at,
        ]);

        return $investment->fresh();
    }

    public function delete(Investment $investment): void
    {
        $investment->update(['is_active' => false]);
    }
}
