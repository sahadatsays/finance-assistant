<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavingsGoalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{id: int, name: string, current_amount: float, target_amount: float, percentage: float, color: string, target_date: string|null, status: string} $goal */
        $goal = $this->resource;

        return [
            'id' => $goal['id'],
            'name' => $goal['name'],
            'current_amount' => $goal['current_amount'],
            'target_amount' => $goal['target_amount'],
            'percentage' => $goal['percentage'],
            'color' => $goal['color'],
            'target_date' => $goal['target_date'],
            'status' => $goal['status'],
        ];
    }
}
