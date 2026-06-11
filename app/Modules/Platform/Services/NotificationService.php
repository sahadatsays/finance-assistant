<?php

namespace App\Modules\Platform\Services;

use App\Models\Platform\AppNotification;
use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationService
{
    public function listForUser(Tenant $tenant, User $user, ?Carbon $since = null): Collection
    {
        $query = AppNotification::query()
            ->where('tenant_id', $tenant->id)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($since !== null) {
            $query->where('updated_at', '>', $since);
        }

        return $query->limit(100)->get();
    }

    public function markRead(User $user, array $ids = []): int
    {
        $query = AppNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at');

        if ($ids !== []) {
            $query->whereIn('id', $ids);
        }

        return $query->update(['read_at' => now()]);
    }

    public function create(Tenant $tenant, User $user, array $data): AppNotification
    {
        return AppNotification::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => $data['data'] ?? null,
        ]);
    }
}
