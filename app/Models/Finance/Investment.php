<?php

namespace App\Models\Finance;

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\InvestmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id', 'name', 'symbol', 'type', 'quantity', 'cost_basis',
    'current_price', 'purchased_at', 'is_active', 'created_by',
])]
class Investment extends Model
{
    protected function casts(): array
    {
        return [
            'type' => InvestmentType::class,
            'quantity' => 'decimal:8',
            'cost_basis' => 'decimal:2',
            'current_price' => 'decimal:4',
            'purchased_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function marketValue(): float
    {
        return round((float) $this->quantity * (float) $this->current_price, 2);
    }
}
