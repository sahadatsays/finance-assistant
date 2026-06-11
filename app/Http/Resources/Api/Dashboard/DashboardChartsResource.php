<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DashboardChartsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $charts */
        $charts = $this->resource;

        return [
            'income_vs_expense' => collect($charts['income_vs_expense'])
                ->map(fn (array $point): array => [
                    'month' => $point['month'],
                    'month_label' => Carbon::createFromFormat('Y-m', $point['month'])->format('M Y'),
                    'income' => $point['income'],
                    'expense' => $point['expense'],
                ])
                ->all(),
            'monthly_trend' => collect($charts['monthly_trend'])
                ->map(fn (array $point): array => [
                    'month' => $point['month'],
                    'month_label' => Carbon::createFromFormat('Y-m', $point['month'])->format('M Y'),
                    'net' => $point['net'],
                ])
                ->all(),
            'category_breakdown' => $charts['category_breakdown'],
        ];
    }
}
