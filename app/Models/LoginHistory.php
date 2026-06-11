<?php

namespace App\Models;

use App\Enums\LoginMethod;
use App\Enums\LoginStatus;
use Database\Factories\LoginHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $email
 * @property int|null $user_device_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property LoginMethod $login_method
 * @property LoginStatus $status
 * @property string|null $failure_reason
 * @property Carbon $logged_in_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'email',
    'user_device_id',
    'ip_address',
    'user_agent',
    'login_method',
    'status',
    'failure_reason',
    'logged_in_at',
])]
class LoginHistory extends Model
{
    /** @use HasFactory<LoginHistoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'login_method' => LoginMethod::class,
            'status' => LoginStatus::class,
            'logged_in_at' => 'datetime',
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
     * @return BelongsTo<UserDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'user_device_id');
    }
}
