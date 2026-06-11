<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{spent: float, budgeted: float, percentage: float, status: string} $status */
        $status = $this->resource;

        return [
            'spent' => $status['spent'],
            'budgeted' => $status['budgeted'],
            'percentage' => $status['percentage'],
            'status' => $status['status'],
        ];
    }
}
