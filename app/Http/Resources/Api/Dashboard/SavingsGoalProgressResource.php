<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalProgressResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{summary: array<string, mixed>, goals: list<array<string, mixed>>} $progress */
        $progress = $this->resource;

        return [
            'summary' => $progress['summary'],
            'goals' => SavingsGoalResource::collection($progress['goals']),
        ];
    }
}
