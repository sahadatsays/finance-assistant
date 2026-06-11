<?php

namespace App\Modules\Tenant\Services;

use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Enums\SubscriptionStatus;
use InvalidArgumentException;

class SubscriptionService
{
    public function createForTenant(Tenant $tenant, ?int $planId = null): Subscription
    {
        $plan = $planId !== null
            ? Plan::query()->where('is_active', true)->findOrFail($planId)
            : $this->defaultPlan();

        return Subscription::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Trialing,
            'quantity' => 1,
            'trial_ends_at' => $tenant->trial_ends_at,
            'starts_at' => null,
        ]);
    }

    public function changePlan(Tenant $tenant, int $planId): Subscription
    {
        $plan = Plan::query()->where('is_active', true)->findOrFail($planId);
        $subscription = $tenant->subscription;

        if ($subscription === null) {
            return $this->createForTenant($tenant, $planId);
        }

        $subscription->update([
            'plan_id' => $plan->id,
            'status' => $subscription->status === SubscriptionStatus::Trialing
                ? SubscriptionStatus::Trialing
                : SubscriptionStatus::Active,
        ]);

        return $subscription->fresh(['plan']);
    }

    public function activate(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $subscription->starts_at ?? now(),
            'trial_ends_at' => null,
        ]);

        return $subscription->fresh(['plan']);
    }

    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'ends_at' => now(),
        ]);

        return $subscription->fresh(['plan']);
    }

    private function defaultPlan(): Plan
    {
        $plan = Plan::query()
            ->where('slug', config('tenancy.default_plan_slug', 'free'))
            ->where('is_active', true)
            ->first();

        if ($plan === null) {
            throw new InvalidArgumentException('Default plan is not configured. Run PlanSeeder.');
        }

        return $plan;
    }
}
