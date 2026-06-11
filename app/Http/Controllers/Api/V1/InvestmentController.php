<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Investment\StoreInvestmentRequest;
use App\Http\Requests\Api\Investment\UpdateInvestmentRequest;
use App\Models\Finance\Investment;
use App\Modules\Finance\Resources\InvestmentResource;
use App\Modules\Finance\Services\InvestmentService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvestmentController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private InvestmentService $investments,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Investment::class, $tenant]);

        return $this->success(
            data: ['investments' => InvestmentResource::collection($this->investments->listForTenant($tenant))],
            message: 'Investments retrieved successfully.',
        );
    }

    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Investment::class, $tenant]);

        $investment = $this->investments->create($tenant, $request->validated(), $request->user());

        return $this->success(
            data: ['investment' => new InvestmentResource($investment)],
            message: 'Investment created successfully.',
            status: 201,
        );
    }

    public function update(UpdateInvestmentRequest $request, int $investment): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->investments->findForTenant($tenant, $investment);

        if ($model === null) {
            return $this->error('Investment not found.', 404);
        }

        $this->authorize('update', $model);

        return $this->success(
            data: ['investment' => new InvestmentResource($this->investments->update($model, $request->validated()))],
            message: 'Investment updated successfully.',
        );
    }

    public function destroy(Request $request, int $investment): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->investments->findForTenant($tenant, $investment);

        if ($model === null) {
            return $this->error('Investment not found.', 404);
        }

        $this->authorize('delete', $model);
        $this->investments->delete($model);

        return $this->success(message: 'Investment deleted successfully.');
    }
}
