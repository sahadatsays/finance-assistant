<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Investment;
use App\Models\Platform\Tenant;

class PortfolioAnalyticsService
{
    public function performance(Tenant $tenant): array
    {
        $investments = Investment::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $totalValue = round($investments->sum(fn (Investment $i) => $i->marketValue()), 2);
        $totalCost = round($investments->sum(fn (Investment $i) => (float) $i->cost_basis), 2);
        $gainLoss = round($totalValue - $totalCost, 2);

        return [
            'total_value' => $totalValue,
            'total_cost' => $totalCost,
            'gain_loss' => $gainLoss,
            'gain_loss_percent' => $totalCost > 0 ? round(($gainLoss / $totalCost) * 100, 2) : 0.0,
            'holdings_count' => $investments->count(),
        ];
    }

    public function allocation(Tenant $tenant): array
    {
        $investments = Investment::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        $totalValue = max($investments->sum(fn (Investment $i) => $i->marketValue()), 0.01);

        $byType = $investments->groupBy(fn (Investment $i) => $i->type->value)->map(function ($group, $type) use ($totalValue) {
            $value = round($group->sum(fn (Investment $i) => $i->marketValue()), 2);

            return [
                'type' => $type,
                'value' => $value,
                'percentage' => round(($value / $totalValue) * 100, 1),
            ];
        })->values()->all();

        $bySymbol = $investments->map(function (Investment $i) use ($totalValue) {
            $value = $i->marketValue();

            return [
                'symbol' => $i->symbol ?? $i->name,
                'name' => $i->name,
                'value' => $value,
                'percentage' => round(($value / $totalValue) * 100, 1),
            ];
        })->sortByDesc('value')->values()->all();

        return [
            'by_type' => $byType,
            'by_symbol' => $bySymbol,
        ];
    }
}
