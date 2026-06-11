<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\Dashboard\DashboardResource;
use App\Services\Finance\TenantDashboardService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __construct(
        private TenantContextService $tenantContext,
        private TenantDashboardService $dashboard,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $this->tenantContext->resolveForUser($user, $request);

        if ($tenant === null) {
            return $this->error('No workspace available.', 403);
        }

        if (! $tenant->isAccessible() && ! $user->isPlatformAdmin()) {
            return $this->error('This workspace is not currently accessible.', 403);
        }

        if (! $user->isPlatformAdmin() && ! $user->belongsToTenant($tenant)) {
            return $this->error('You are not a member of this workspace.', 403);
        }

        $payload = $this->dashboard->forApi($tenant);

        return $this->success(
            data: new DashboardResource($payload),
            message: 'Dashboard retrieved successfully.',
            meta: [
                'period' => now()->format('Y-m'),
                'cache_enabled' => config('api.dashboard.cache_enabled', true),
                'cache_ttl' => config('api.dashboard.cache_ttl', 300),
            ],
        );
    }
}
