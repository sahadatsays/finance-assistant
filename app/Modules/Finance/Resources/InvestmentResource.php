<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Investment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Investment */
class InvestmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'type' => $this->type->value,
            'quantity' => (float) $this->quantity,
            'cost_basis' => (float) $this->cost_basis,
            'current_price' => (float) $this->current_price,
            'market_value' => $this->marketValue(),
            'purchased_at' => $this->purchased_at?->toDateString(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
