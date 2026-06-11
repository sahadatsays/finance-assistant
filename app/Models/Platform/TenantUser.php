<?php

namespace App\Models\Platform;

use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;
use Database\Factories\Platform\TenantUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $user_id
 * @property TenantUserRole $role
 * @property Carbon|null $invited_at
 * @property Carbon|null $joined_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tenant_id', 'user_id', 'role', 'invited_at', 'joined_at'])]
class TenantUser extends Pivot
{
    /** @use HasFactory<TenantUserFactory> */
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'tenant_users';

    /**
     * @var bool
     */
    public $incrementing = true;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => TenantUserRole::class,
            'invited_at' => 'datetime',
            'joined_at' => 'datetime',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): TenantUserFactory
    {
        return TenantUserFactory::new();
    }
}
