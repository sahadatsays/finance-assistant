<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Models\Finance\Account;
use App\Modules\Finance\Services\ReportService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NetWorthController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private ReportService $reports,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Account::class, $tenant]);

        return $this->success(
            data: ['net_worth' => $this->reports->netWorth($tenant)],
            message: 'Net worth retrieved successfully.',
        );
    }

    public function history(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Account::class, $tenant]);

        $months = (int) $request->query('months', 6);

        return $this->success(
            data: ['net_worth' => $this->reports->netWorthHistory($tenant, $months)],
            message: 'Net worth history retrieved successfully.',
        );
    }
}
