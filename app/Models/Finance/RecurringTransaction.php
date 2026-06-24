<?php

namespace App\Models\Finance;

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\RecurrenceFrequency;
use App\Modules\Finance\Enums\TransactionType;
use Database\Factories\Finance\RecurringTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property TransactionType $type
 * @property string $amount
 * @property int $account_id
 * @property int $category_id
 * @property RecurrenceFrequency $frequency
 * @property string $run_time
 * @property Carbon $start_date
 * @property Carbon $next_run_at
 * @property Carbon|null $last_run_at
 * @property string|null $notes
 * @property bool $is_active
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'tenant_id',
    'name',
    'type',
    'amount',
    'account_id',
    'category_id',
    'frequency',
    'run_time',
    'start_date',
    'next_run_at',
    'last_run_at',
    'notes',
    'is_active',
    'created_by',
    'updated_by',
])]
class RecurringTransaction extends Model
{
    /** @use HasFactory<RecurringTransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'frequency' => RecurrenceFrequency::class,
            'start_date' => 'date',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
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
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
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

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
