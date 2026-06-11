<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Bill;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bill
 */
class BillResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => (float) $this->amount,
            'due_date' => $this->due_date->toDateString(),
            'recurrence' => $this->recurrence->value,
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'notes' => $this->notes,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'color' => $this->category->color,
            ] : null),
        ];
    }
}
