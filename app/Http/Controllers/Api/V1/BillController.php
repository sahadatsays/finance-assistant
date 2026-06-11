<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Bill\ListBillsRequest;
use App\Http\Requests\Api\Bill\StoreBillRequest;
use App\Http\Requests\Api\Bill\UpdateBillRequest;
use App\Models\Finance\Bill;
use App\Modules\Finance\Resources\BillResource;
use App\Modules\Finance\Services\BillService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private BillService $bills,
    ) {}

    public function index(ListBillsRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Bill::class, $tenant]);

        $paginator = $this->bills->paginateForTenant(
            $tenant,
            $request->filters(),
            (int) $request->validated('per_page', 15),
        );

        return $this->paginated(
            $paginator->through(fn (Bill $bill) => new BillResource($bill)),
            message: 'Bills retrieved successfully.',
        );
    }

    public function upcoming(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Bill::class, $tenant]);

        $days = (int) $request->query('days', 30);
        $bills = $this->bills->upcomingForTenant($tenant, max(1, min($days, 90)));

        return $this->success(
            data: ['bills' => BillResource::collection($bills)],
            message: 'Upcoming bills retrieved successfully.',
        );
    }

    public function store(StoreBillRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Bill::class, $tenant]);

        $bill = $this->bills->create($tenant, $request->validated(), $request->user());

        return $this->success(
            data: ['bill' => new BillResource($bill)],
            message: 'Bill created successfully.',
            status: 201,
        );
    }

    public function update(UpdateBillRequest $request, int $bill): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->bills->findForTenant($tenant, $bill);

        if ($model === null) {
            return $this->error('Bill not found.', 404);
        }

        $this->authorize('update', $model);

        $model = $this->bills->update($model, $request->validated(), $request->user());

        return $this->success(
            data: ['bill' => new BillResource($model)],
            message: 'Bill updated successfully.',
        );
    }

    public function destroy(Request $request, int $bill): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->bills->findForTenant($tenant, $bill);

        if ($model === null) {
            return $this->error('Bill not found.', 404);
        }

        $this->authorize('delete', $model);
        $this->bills->delete($model, $request->user());

        return $this->success(message: 'Bill deleted successfully.');
    }

    public function markPaid(Request $request, int $bill): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->bills->findForTenant($tenant, $bill);

        if ($model === null) {
            return $this->error('Bill not found.', 404);
        }

        $this->authorize('update', $model);

        $model = $this->bills->markPaid($model, $request->user());

        return $this->success(
            data: ['bill' => new BillResource($model)],
            message: 'Bill marked as paid.',
        );
    }
}
