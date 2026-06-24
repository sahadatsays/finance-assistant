<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Account
 */
class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'balance' => (float) $this->balance,
            'currency' => $this->currency,
            'transactions_count' => (int) ($this->transactions_count ?? 0),
            'can_delete' => (int) ($this->transactions_count ?? 0) === 0,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
