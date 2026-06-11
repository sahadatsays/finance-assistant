<?php

namespace App\Models\Finance;

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\BillRecurrence;
use App\Modules\Finance\Enums\BillStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $amount
 * @property Carbon $due_date
 * @property BillRecurrence $recurrence
 * @property BillStatus $status
 * @property Carbon|null $paid_at
 * @property int|null $category_id
 * @property string|null $notes
 * @property bool $is_active
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'tenant_id',
    'name',
    'amount',
    'due_date',
    'recurrence',
    'status',
    'paid_at',
    'category_id',
    'notes',
    'is_active',
    'created_by',
])]
class Bill extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'recurrence' => BillRecurrence::class,
            'status' => BillStatus::class,
            'paid_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
