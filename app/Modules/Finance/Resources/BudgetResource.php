<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Budget;
use App\Modules\Finance\Services\BudgetAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Budget
 */
class BudgetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $analytics = app(BudgetAnalyticsService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'period_type' => $this->period_type->value,
            'period_start' => $this->period_start->toDateString(),
            'period_end' => $this->period_end->toDateString(),
            'amount' => (float) $this->amount,
            'is_active' => $this->is_active,
            'utilization' => $analytics->utilization($this->resource),
            'categories' => $analytics->categoryProgress($this->resource),
            'lines' => $this->whenLoaded('lines', fn () => $this->lines->map(fn ($line) => [
                'id' => $line->id,
                'category_id' => $line->category_id,
                'category' => $line->category?->name,
                'color' => $line->category?->color,
                'amount' => (float) $line->amount,
            ])),
        ];
    }
}
