<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Models\Finance\Investment;
use App\Modules\Finance\Services\PortfolioAnalyticsService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private PortfolioAnalyticsService $portfolio,
    ) {}

    public function performance(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Investment::class, $tenant]);

        return $this->success(
            data: ['performance' => $this->portfolio->performance($tenant)],
            message: 'Portfolio performance retrieved successfully.',
        );
    }

    public function allocation(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Investment::class, $tenant]);

        return $this->success(
            data: ['allocation' => $this->portfolio->allocation($tenant)],
            message: 'Portfolio allocation retrieved successfully.',
        );
    }
}
