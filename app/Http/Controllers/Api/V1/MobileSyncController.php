<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Sync\SyncRequest;
use App\Models\Platform\Tenant;
use App\Modules\Platform\Services\MobileSyncService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class MobileSyncController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private MobileSyncService $sync,
    ) {}

    public function transactions(SyncRequest $request): JsonResponse
    {
        return $this->syncResponse($request, fn ($tenant, $since) => $this->sync->transactions($tenant, $since));
    }

    public function budgets(SyncRequest $request): JsonResponse
    {
        return $this->syncResponse($request, fn ($tenant, $since) => $this->sync->budgets($tenant, $since));
    }

    public function goals(SyncRequest $request): JsonResponse
    {
        return $this->syncResponse($request, fn ($tenant, $since) => $this->sync->goals($tenant, $since));
    }

    public function dashboard(SyncRequest $request): JsonResponse
    {
        return $this->syncResponse($request, fn ($tenant, $since) => $this->sync->dashboard($tenant, $since));
    }

    public function notifications(SyncRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $since = $this->parseSince($request);

        $payload = $this->sync->notifications($tenant, $request->user(), $since);

        return $this->success(
            data: $payload,
            message: 'Notifications synced successfully.',
            meta: $this->syncMeta($payload),
        );
    }

    /**
     * @param  callable(Tenant, ?Carbon): array<string, mixed>  $callback
     */
    private function syncResponse(SyncRequest $request, callable $callback): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $since = $this->parseSince($request);
        $payload = $callback($tenant, $since);

        return $this->success(
            data: $payload,
            message: 'Sync completed successfully.',
            meta: $this->syncMeta($payload),
        );
    }

    private function parseSince(SyncRequest $request): ?Carbon
    {
        $since = $request->validated('since');

        return $since !== null ? Carbon::parse($since) : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function syncMeta(array $payload): array
    {
        return [
            'server_time' => now()->toIso8601String(),
            'synced_at' => $payload['synced_at'] ?? now()->toIso8601String(),
            'delta' => true,
        ];
    }
}
