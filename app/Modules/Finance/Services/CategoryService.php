<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use App\Services\Platform\ActivityLogService;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(
        private ActivityLogService $activityLog,
        private SystemCategoryService $systemCategories,
    ) {}

    /**
     * @return Collection<int, Category>
     */
    public function listForTenant(Tenant $tenant, bool $includeArchived = false): Collection
    {
        $query = Category::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_system')
            ->orderBy('type')
            ->orderBy('name');

        if (! $includeArchived) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function listArchivedForTenant(Tenant $tenant): Collection
    {
        return Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', false)
            ->orderByDesc('archived_at')
            ->get();
    }

    public function ensureSystemCategories(Tenant $tenant): void
    {
        $this->systemCategories->seedForTenant($tenant);
    }

    /**
     * @param  array{name: string, type: string, color: string, icon?: string|null}  $data
     */
    public function create(Tenant $tenant, array $data, User $user): Category
    {
        $type = CategoryType::from($data['type']);

        if (Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('name', $data['name'])
            ->where('type', $type)
            ->exists()) {
            throw new InvalidArgumentException('A category with this name already exists for the selected type.');
        }

        $category = Category::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'type' => $type,
            'color' => $data['color'],
            'icon' => $data['icon'] ?? null,
            'is_system' => false,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $this->activityLog->log(
            "Category \"{$category->name}\" was created.",
            logName: 'finance',
            subject: $category,
            causer: $user,
            tenant: $tenant,
            properties: ['type' => $category->type->value, 'kind' => 'custom'],
        );

        return $category;
    }

    /**
     * @param  array{name?: string, color?: string, icon?: string|null}  $data
     */
    public function update(Category $category, array $data, User $user): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            if (Category::query()
                ->where('tenant_id', $category->tenant_id)
                ->where('type', $category->type)
                ->where('name', $data['name'])
                ->where('id', '!=', $category->id)
                ->exists()) {
                throw new InvalidArgumentException('A category with this name already exists for the selected type.');
            }
        }

        $category->update($data);

        $this->activityLog->log(
            "Category \"{$category->name}\" was updated.",
            logName: 'finance',
            subject: $category,
            causer: $user,
            tenant: $category->tenant,
            properties: ['changes' => array_keys($data)],
        );

        return $category->fresh();
    }

    public function archive(Category $category, User $user): Category
    {
        if (! $category->is_active) {
            return $category;
        }

        $category->update([
            'is_active' => false,
            'archived_at' => now(),
        ]);

        $this->activityLog->log(
            "Category \"{$category->name}\" was archived.",
            logName: 'finance',
            subject: $category,
            causer: $user,
            tenant: $category->tenant,
        );

        return $category->fresh();
    }

    public function restore(Category $category, User $user): Category
    {
        if ($category->is_active) {
            return $category;
        }

        $category->update([
            'is_active' => true,
            'archived_at' => null,
        ]);

        $this->activityLog->log(
            "Category \"{$category->name}\" was restored.",
            logName: 'finance',
            subject: $category,
            causer: $user,
            tenant: $category->tenant,
        );

        return $category->fresh();
    }

    public function delete(Category $category, User $user): void
    {
        if ($category->is_system) {
            throw new InvalidArgumentException('System categories cannot be deleted.');
        }

        if ($category->transactions()->exists()) {
            throw new InvalidArgumentException('Categories with transactions cannot be deleted. Archive instead.');
        }

        $name = $category->name;
        $tenant = $category->tenant;

        $category->delete();

        $this->activityLog->log(
            "Category \"{$name}\" was deleted.",
            logName: 'finance',
            causer: $user,
            tenant: $tenant,
            properties: ['name' => $name],
        );
    }

    public function belongsToTenant(Category $category, Tenant $tenant): bool
    {
        return $category->tenant_id === $tenant->id;
    }
}
