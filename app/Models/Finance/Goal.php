<?php

namespace App\Models\Finance;

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\GoalType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property GoalType $type
 * @property string $target_amount
 * @property string $current_amount
 * @property Carbon|null $target_date
 * @property string $color
 * @property bool $is_active
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tenant_id', 'name', 'type', 'target_amount', 'current_amount', 'target_date', 'color', 'is_active', 'created_by'])]
class Goal extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GoalType::class,
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'target_date' => 'date',
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<GoalContribution, $this>
     */
    public function contributions(): HasMany
    {
        return $this->hasMany(GoalContribution::class);
    }

    public function isCompleted(): bool
    {
        return (float) $this->current_amount >= (float) $this->target_amount;
    }
}
