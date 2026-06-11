<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Tag;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagService
{
    /**
     * @param  list<string>  $tagNames
     */
    public function syncForTransaction(Tenant $tenant, Transaction $transaction, array $tagNames): void
    {
        $tagIds = collect($tagNames)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name) => $this->findOrCreate($tenant, $name)->id)
            ->all();

        $transaction->tags()->sync($tagIds);
    }

    public function findOrCreate(Tenant $tenant, string $name): Tag
    {
        $slug = Str::slug($name);

        return Tag::query()->firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => $slug],
            ['name' => $name],
        );
    }

    /**
     * @return Collection<int, Tag>
     */
    public function listForTenant(Tenant $tenant): Collection
    {
        return Tag::query()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();
    }
}
