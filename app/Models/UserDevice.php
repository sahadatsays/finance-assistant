<?php

namespace App\Models;

use Database\Factories\UserDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property int $user_id
 * @property string $device_fingerprint
 * @property string|null $name
 * @property string|null $platform
 * @property string|null $browser
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property bool $is_trusted
 * @property Carbon|null $last_active_at
 * @property int|null $token_id
 * @property string|null $session_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'device_fingerprint',
    'name',
    'platform',
    'browser',
    'ip_address',
    'user_agent',
    'is_trusted',
    'last_active_at',
    'token_id',
    'session_id',
])]
class UserDevice extends Model
{
    /** @use HasFactory<UserDeviceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_trusted' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<PersonalAccessToken, $this>
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'token_id');
    }

    /**
     * @return HasMany<LoginHistory, $this>
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * @param  Builder<UserDevice>  $query
     * @return Builder<UserDevice>
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
