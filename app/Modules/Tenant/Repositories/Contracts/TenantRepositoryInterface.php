<?php

namespace App\Modules\Tenant\Repositories\Contracts;

use App\Models\Platform\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TenantRepositoryInterface
{
    public function find(int $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Tenant>
     */
    public function forUser(int $userId): Collection;

    public function create(array $attributes): Tenant;

    public function update(Tenant $tenant, array $attributes): Tenant;
}
