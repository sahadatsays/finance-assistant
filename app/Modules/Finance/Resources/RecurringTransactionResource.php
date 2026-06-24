<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\RecurringTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RecurringTransaction
 */
class RecurringTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'amount' => (float) $this->amount,
            'account' => [
                'id' => $this->account->id,
                'name' => $this->account->name,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'color' => $this->category->color,
            ],
            'frequency' => $this->frequency->value,
            'frequency_label' => $this->frequency->label(),
            'run_time' => substr((string) $this->run_time, 0, 5),
            'start_date' => $this->start_date->toDateString(),
            'next_run_at' => $this->next_run_at->toIso8601String(),
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'transactions_count' => (int) ($this->transactions_count ?? 0),
        ];
    }
}
