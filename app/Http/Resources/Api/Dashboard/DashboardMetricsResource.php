<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardMetricsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $metrics */
        $metrics = $this->resource;

        return [
            'total_income' => $metrics['total_income'],
            'total_expense' => $metrics['total_expense'],
            'total_savings' => $metrics['total_savings'],
            'net_worth' => $metrics['net_worth'],
            'budget_status' => new BudgetStatusResource($metrics['budget_status']),
            'savings_goal_progress' => new SavingsGoalProgressResource($metrics['savings_goal_progress']),
        ];
    }
}
