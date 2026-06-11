<?php

namespace App\Modules\Platform\Services;

use App\Models\Finance\Budget;
use App\Models\Finance\Goal;
use App\Models\Finance\Transaction;
use App\Models\Platform\AppNotification;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Resources\BudgetResource;
use App\Modules\Finance\Resources\GoalResource;
use App\Modules\Finance\Resources\TransactionResource;
use App\Services\Finance\TenantDashboardService;
use Illuminate\Support\Carbon;

class MobileSyncService
{
    public function __construct(
        private TenantDashboardService $dashboard,
        private NotificationService $notifications,
    ) {}

    /**
     * @return array{items: mixed, deleted_ids: list<int>, synced_at: string}
     */
    public function transactions(Tenant $tenant, ?Carbon $since, int $limit = 100): array
    {
        $query = Transaction::query()
            ->with(['category', 'account', 'transferAccount', 'tags'])
            ->where('tenant_id', $tenant->id);

        if ($since !== null) {
            $query->where('updated_at', '>', $since);
        }

        $items = $query->orderByDesc('updated_at')->limit($limit)->get();

        return $this->syncPayload(
            TransactionResource::collection($items)->resolve(),
            [],
            $items->max('updated_at'),
        );
    }

    /**
     * @return array{items: mixed, deleted_ids: list<int>, synced_at: string}
     */
    public function budgets(Tenant $tenant, ?Carbon $since, int $limit = 50): array
    {
        $active = Budget::query()
            ->with(['lines.category'])
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        $deletedIds = [];
        if ($since !== null) {
            $deletedIds = Budget::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', false)
                ->where('updated_at', '>', $since)
                ->pluck('id')
                ->all();
        }

        return $this->syncPayload(
            BudgetResource::collection($active)->resolve(),
            $deletedIds,
            $active->max('updated_at'),
        );
    }

    /**
     * @return array{items: mixed, deleted_ids: list<int>, synced_at: string}
     */
    public function goals(Tenant $tenant, ?Carbon $since, int $limit = 50): array
    {
        $active = Goal::query()
            ->with(['contributions' => fn ($q) => $q->orderByDesc('contributed_at')->limit(5)])
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();

        $deletedIds = [];
        if ($since !== null) {
            $deletedIds = Goal::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', false)
                ->where('updated_at', '>', $since)
                ->pluck('id')
                ->all();
        }

        return $this->syncPayload(
            GoalResource::collection($active)->resolve(),
            $deletedIds,
            $active->max('updated_at'),
        );
    }

    /**
     * @return array{items: array<string, mixed>, deleted_ids: list<int>, synced_at: string}
     */
    public function dashboard(Tenant $tenant, ?Carbon $since): array
    {
        $payload = $this->dashboard->forApi($tenant);
        $payload['last_updated'] = now()->toIso8601String();

        return $this->syncPayload($payload, [], now());
    }

    /**
     * @return array{items: mixed, deleted_ids: list<int>, synced_at: string}
     */
    public function notifications(Tenant $tenant, User $user, ?Carbon $since, int $limit = 100): array
    {
        $items = $this->notifications->listForUser($tenant, $user, $since)->take($limit);

        return $this->syncPayload(
            $items->map(fn (AppNotification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'read_at' => $n->read_at?->toIso8601String(),
                'updated_at' => $n->updated_at?->toIso8601String(),
            ])->all(),
            [],
            $items->max('updated_at'),
        );
    }

    /**
     * @param  list<int>  $deletedIds
     * @return array{items: mixed, deleted_ids: list<int>, synced_at: string}
     */
    private function syncPayload(mixed $items, array $deletedIds, mixed $maxUpdatedAt): array
    {
        return [
            'items' => $items,
            'deleted_ids' => $deletedIds,
            'synced_at' => $maxUpdatedAt
                ? Carbon::parse($maxUpdatedAt)->toIso8601String()
                : now()->toIso8601String(),
        ];
    }
}
