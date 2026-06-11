<?php

namespace App\Modules\Tenant\Repositories\Eloquent;

use App\Models\Platform\Tenant;
use App\Modules\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TenantRepository implements TenantRepositoryInterface
{
    public function find(int $id): ?Tenant
    {
        return Tenant::query()->find($id);
    }

    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::query()->where('slug', $slug)->first();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Tenant::query()
            ->with(['subscription.plan'])
            ->withCount('tenantUsers');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    public function forUser(int $userId): Collection
    {
        return Tenant::query()
            ->whereHas('tenantUsers', fn ($q) => $q->where('user_id', $userId))
            ->with(['subscription.plan'])
            ->orderBy('name')
            ->get();
    }

    public function create(array $attributes): Tenant
    {
        return Tenant::query()->create($attributes);
    }

    public function update(Tenant $tenant, array $attributes): Tenant
    {
        $tenant->update($attributes);

        return $tenant->fresh();
    }
}
