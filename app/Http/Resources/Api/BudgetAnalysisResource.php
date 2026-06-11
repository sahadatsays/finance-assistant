<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetAnalysisResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $analysis */
        $analysis = $this->resource;

        return [
            'budget' => $analysis['budget'],
            'allocated' => $analysis['allocated'],
            'spent' => $analysis['spent'],
            'remaining' => $analysis['remaining'],
            'percentage' => $analysis['percentage'],
            'status' => $analysis['status'],
            'categories' => $analysis['categories'],
        ];
    }
}
