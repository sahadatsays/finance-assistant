<?php

namespace App\Modules\Tenant\Services;

use App\Enums\LoginStatus;
use App\Models\LoginHistory;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;

class TenantUsageService
{
    /**
     * @return array{
     *     users_count: int,
     *     owners_count: int,
     *     logins_last_30_days: int,
     *     last_activity_at: string|null,
     *     plan_slug: string|null,
     *     plan_max_users: int|null,
     *     subscription_status: string|null
     * }
     */
    public function getUsage(Tenant $tenant): array
    {
        $tenant->loadMissing(['subscription.plan', 'tenantUsers']);

        $userIds = $tenant->tenantUsers()->pluck('user_id');
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        $loginsLast30Days = LoginHistory::query()
            ->whereIn('user_id', $userIds)
            ->where('status', LoginStatus::Success)
            ->where('logged_in_at', '>=', $thirtyDaysAgo)
            ->count();

        $lastActivity = LoginHistory::query()
            ->whereIn('user_id', $userIds)
            ->where('status', LoginStatus::Success)
            ->max('logged_in_at');

        return [
            'users_count' => $tenant->tenantUsers()->count(),
            'owners_count' => $tenant->tenantUsers()->where('role', 'tenant-owner')->count(),
            'logins_last_30_days' => $loginsLast30Days,
            'last_activity_at' => $lastActivity ? Carbon::parse($lastActivity)->toIso8601String() : null,
            'plan_slug' => $tenant->subscription?->plan?->slug,
            'plan_max_users' => $tenant->subscription?->plan?->max_users,
            'subscription_status' => $tenant->subscription?->status?->value,
        ];
    }
}
