<?php

namespace App\Modules\Tenant\Services;

use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantStatus;
use App\Modules\Tenant\Enums\TenantUserRole;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Services\Platform\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TenantService
{
    public function __construct(
        private TenantRepositoryInterface $tenants,
        private SubscriptionService $subscriptions,
        private TenantUserService $tenantUsers,
        private ActivityLogService $activityLog,
    ) {}

    /**
     * @param  array{name: string, slug?: string, plan_id?: int, owner_user_id?: int, owner_email?: string, owner_name?: string, settings?: array<string, mixed>}  $data
     */
    public function create(array $data, ?User $createdBy = null): Tenant
    {
        return DB::transaction(function () use ($data, $createdBy): Tenant {
            $slug = $data['slug'] ?? Str::slug($data['name']);

            if ($this->tenants->findBySlug($slug) !== null) {
                $slug = $slug.'-'.Str::lower(Str::random(4));
            }

            $tenant = $this->tenants->create([
                'name' => $data['name'],
                'slug' => $slug,
                'status' => TenantStatus::Trial,
                'settings' => $data['settings'] ?? [
                    'timezone' => 'UTC',
                    'locale' => 'en',
                    'currency' => 'USD',
                ],
                'trial_ends_at' => now()->addDays(config('tenancy.trial_days', 14)),
                'created_by' => $createdBy?->id,
            ]);

            $this->subscriptions->createForTenant($tenant, $data['plan_id'] ?? null);

            if (! empty($data['owner_user_id'])) {
                $owner = User::query()->findOrFail($data['owner_user_id']);
                $this->tenantUsers->attach($tenant, $owner, TenantUserRole::Owner);
            } elseif (! empty($data['owner_email'])) {
                $owner = User::query()->firstOrCreate(
                    ['email' => $data['owner_email']],
                    [
                        'name' => $data['owner_name'] ?? Str::before($data['owner_email'], '@'),
                        'password' => Str::password(16),
                    ],
                );

                if ($owner->profile === null) {
                    $owner->profile()->create([]);
                }

                $this->tenantUsers->attach($tenant, $owner, TenantUserRole::Owner);
            }

            $tenant = $tenant->load(['subscription.plan', 'tenantUsers.user']);

            $this->activityLog->log(
                "Tenant \"{$tenant->name}\" was created.",
                subject: $tenant,
                causer: $createdBy,
                tenant: $tenant,
            );

            return $tenant;
        });
    }

    public function suspend(Tenant $tenant, ?User $causer = null): Tenant
    {
        if ($tenant->status === TenantStatus::Suspended) {
            return $tenant;
        }

        $tenant = $this->tenants->update($tenant, [
            'status' => TenantStatus::Suspended,
            'suspended_at' => now(),
        ]);

        $this->activityLog->log(
            "Tenant \"{$tenant->name}\" was suspended.",
            subject: $tenant,
            causer: $causer,
            tenant: $tenant,
        );

        return $tenant;
    }

    public function activate(Tenant $tenant, ?User $causer = null): Tenant
    {
        if ($tenant->status === TenantStatus::Cancelled) {
            throw new InvalidArgumentException('Cancelled tenants cannot be reactivated without manual intervention.');
        }

        $tenant = $this->tenants->update($tenant, [
            'status' => TenantStatus::Active,
            'suspended_at' => null,
        ]);

        $this->activityLog->log(
            "Tenant \"{$tenant->name}\" was activated.",
            subject: $tenant,
            causer: $causer,
            tenant: $tenant,
        );

        return $tenant;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function updateSettings(Tenant $tenant, array $settings): Tenant
    {
        $merged = array_merge($tenant->settings ?? [], $settings);

        return $this->tenants->update($tenant, ['settings' => $merged]);
    }

    /**
     * @param  array{name?: string, slug?: string}  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        if (isset($data['slug']) && $data['slug'] !== $tenant->slug) {
            $existing = $this->tenants->findBySlug($data['slug']);

            if ($existing !== null && $existing->id !== $tenant->id) {
                throw new InvalidArgumentException('Tenant slug is already taken.');
            }
        }

        return $this->tenants->update($tenant, $data);
    }

    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function listForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->tenants->paginate($filters, $perPage);
    }

    /**
     * @return Collection<int, Tenant>
     */
    public function listForUser(User $user): Collection
    {
        return $this->tenants->forUser($user->id);
    }
}
