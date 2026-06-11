<?php

namespace App\Services\Platform;

use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\SubscriptionStatus;
use App\Modules\Tenant\Enums\TenantStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * @return array{
     *     total_tenants: int,
     *     active_tenants: int,
     *     trial_tenants: int,
     *     revenue: float,
     *     total_users: int,
     *     new_registrations: int
     * }
     */
    public function metrics(): array
    {
        $revenue = Subscription::query()
            ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
            ->where('subscriptions.status', SubscriptionStatus::Active)
            ->sum('plans.price_monthly');

        return [
            'total_tenants' => Tenant::query()->count(),
            'active_tenants' => Tenant::query()->where('status', TenantStatus::Active)->count(),
            'trial_tenants' => Tenant::query()->where('status', TenantStatus::Trial)->count(),
            'revenue' => round((float) $revenue, 2),
            'total_users' => User::query()->where('is_platform_admin', false)->count(),
            'new_registrations' => User::query()
                ->where('is_platform_admin', false)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
        ];
    }

    /**
     * @return array{
     *     growth: list<array{month: string, tenants: int}>,
     *     registrations: list<array{month: string, users: int}>,
     *     revenue: list<array{month: string, revenue: float}>,
     *     tenant_statistics: list<array{status: string, count: int, label: string}>
     * }
     */
    public function charts(): array
    {
        return [
            'growth' => $this->tenantGrowthChart(),
            'registrations' => $this->registrationChart(),
            'revenue' => $this->revenueChart(),
            'tenant_statistics' => $this->tenantStatisticsChart(),
        ];
    }

    /**
     * @return list<array{month: string, tenants: int}>
     */
    private function tenantGrowthChart(): array
    {
        $months = $this->lastTwelveMonths();
        $since = Carbon::now()->subMonths(11)->startOfMonth();

        $counts = DB::connection()->getDriverName() === 'sqlite'
            ? Tenant::query()
                ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as total")
                ->where('created_at', '>=', $since)
                ->groupBy('month')
                ->pluck('total', 'month')
            : Tenant::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
                ->where('created_at', '>=', $since)
                ->groupBy('month')
                ->pluck('total', 'month');

        return $months->map(fn (string $month) => [
            'month' => $month,
            'tenants' => (int) ($counts[$month] ?? 0),
        ])->all();
    }

    /**
     * @return list<array{month: string, users: int}>
     */
    private function registrationChart(): array
    {
        $months = $this->lastTwelveMonths();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $counts = User::query()
                ->selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as total")
                ->where('is_platform_admin', false)
                ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->pluck('total', 'month');
        } else {
            $counts = User::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
                ->where('is_platform_admin', false)
                ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->pluck('total', 'month');
        }

        return $months->map(fn (string $month) => [
            'month' => $month,
            'users' => (int) ($counts[$month] ?? 0),
        ])->all();
    }

    /**
     * @return list<array{month: string, revenue: float}>
     */
    private function revenueChart(): array
    {
        $months = $this->lastTwelveMonths();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $amounts = Subscription::query()
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->selectRaw("strftime('%Y-%m', subscriptions.created_at) as month, SUM(plans.price_monthly) as total")
                ->where('subscriptions.status', SubscriptionStatus::Active->value)
                ->where('subscriptions.created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->pluck('total', 'month');
        } else {
            $amounts = Subscription::query()
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->selectRaw("DATE_FORMAT(subscriptions.created_at, '%Y-%m') as month, SUM(plans.price_monthly) as total")
                ->where('subscriptions.status', SubscriptionStatus::Active->value)
                ->where('subscriptions.created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->pluck('total', 'month');
        }

        return $months->map(fn (string $month) => [
            'month' => $month,
            'revenue' => round((float) ($amounts[$month] ?? 0), 2),
        ])->all();
    }

    /**
     * @return list<array{status: string, count: int, label: string}>
     */
    private function tenantStatisticsChart(): array
    {
        $counts = Tenant::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(TenantStatus::cases())->map(fn (TenantStatus $status) => [
            'status' => $status->value,
            'label' => ucfirst($status->value),
            'count' => (int) ($counts[$status->value] ?? 0),
        ])->all();
    }

    /**
     * @return Collection<int, string>
     */
    private function lastTwelveMonths(): Collection
    {
        return collect(range(11, 0))->map(
            fn (int $i) => Carbon::now()->subMonths($i)->format('Y-m'),
        );
    }
}
