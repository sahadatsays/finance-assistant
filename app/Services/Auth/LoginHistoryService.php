<?php

namespace App\Services\Auth;

use App\Enums\LoginMethod;
use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class LoginHistoryService
{
    public function __construct(
        private DeviceTrackingService $deviceTracking,
    ) {}

    /**
     * Record a successful login attempt.
     */
    public function recordSuccess(
        User $user,
        Request $request,
        LoginMethod $method = LoginMethod::Password,
        ?UserDevice $device = null,
    ): LoginHistory {
        $device ??= $this->deviceTracking->track($user, $request);

        return LoginHistory::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'user_device_id' => $device->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_method' => $method,
            'status' => LoginStatus::Success,
            'logged_in_at' => now(),
        ]);
    }

    /**
     * Record a failed login attempt.
     */
    public function recordFailure(
        Request $request,
        ?User $user = null,
        ?string $email = null,
        ?string $failureReason = null,
        LoginMethod $method = LoginMethod::Password,
    ): LoginHistory {
        return LoginHistory::query()->create([
            'user_id' => $user?->id,
            'email' => $email ?? $user?->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_method' => $method,
            'status' => LoginStatus::Failed,
            'failure_reason' => $failureReason,
            'logged_in_at' => now(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, LoginHistory>
     */
    public function paginateForUser(User $user, int $perPage = 15)
    {
        return $user->loginHistories()
            ->with('device')
            ->latest('logged_in_at')
            ->paginate($perPage);
    }
}
