<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Goal;
use App\Modules\Finance\Services\GoalAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Goal
 */
class GoalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $analytics = app(GoalAnalyticsService::class);
        $summary = $analytics->goalSummary($this->resource);

        return [
            ...$summary,
            'contributions' => $this->whenLoaded('contributions', fn () => $this->contributions->map(fn ($c) => [
                'id' => $c->id,
                'amount' => (float) $c->amount,
                'notes' => $c->notes,
                'contributed_at' => $c->contributed_at->toIso8601String(),
            ])),
            'contribution_trend' => $analytics->contributionTrend($this->resource),
        ];
    }
}
