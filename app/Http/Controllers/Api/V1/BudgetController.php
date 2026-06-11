<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Budget\ListBudgetsRequest;
use App\Http\Requests\Api\Budget\StoreBudgetRequest;
use App\Http\Requests\Api\Budget\UpdateBudgetRequest;
use App\Http\Resources\Api\BudgetAnalysisResource;
use App\Models\Finance\Budget;
use App\Modules\Finance\Resources\BudgetResource;
use App\Modules\Finance\Services\BudgetAnalyticsService;
use App\Modules\Finance\Services\BudgetService;
use App\Modules\Finance\Services\CategoryService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BudgetController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private BudgetService $budgets,
        private BudgetAnalyticsService $analytics,
        private CategoryService $categories,
    ) {}

    public function index(ListBudgetsRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Budget::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $filters = $request->filters();
        $perPage = (int) $request->validated('per_page', 15);

        $paginator = $this->budgets->paginateForTenant($tenant, $filters, $perPage);

        return $this->paginated(
            $paginator->through(fn (Budget $budget) => new BudgetResource($budget)),
            message: 'Budgets retrieved successfully.',
            meta: [
                'filters' => array_merge([
                    'period_type' => null,
                    'sort' => 'period_start',
                    'direction' => 'desc',
                ], $filters),
            ],
        );
    }

    public function show(Request $request, int $budget): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->budgets->findForTenant($tenant, $budget);

        if ($model === null) {
            return $this->error('Budget not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['budget' => new BudgetResource($model)],
            message: 'Budget retrieved successfully.',
        );
    }

    public function analysis(Request $request, int $budget): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->budgets->findForTenant($tenant, $budget);

        if ($model === null) {
            return $this->error('Budget not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['analysis' => new BudgetAnalysisResource($this->analytics->analysis($model))],
            message: 'Budget analysis retrieved successfully.',
        );
    }

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Budget::class, $tenant]);

        try {
            $budget = $this->budgets->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['budget' => new BudgetResource($budget)],
            message: 'Budget created successfully.',
            status: 201,
        );
    }

    public function update(UpdateBudgetRequest $request, int $budget): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->budgets->findForTenant($tenant, $budget);

        if ($model === null) {
            return $this->error('Budget not found.', 404);
        }

        $this->authorize('update', $model);

        try {
            $model = $this->budgets->update($model, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['budget' => new BudgetResource($model)],
            message: 'Budget updated successfully.',
        );
    }

    public function destroy(Request $request, int $budget): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->budgets->findForTenant($tenant, $budget);

        if ($model === null) {
            return $this->error('Budget not found.', 404);
        }

        $this->authorize('delete', $model);

        $this->budgets->delete($model, $request->user());

        return $this->success(message: 'Budget deleted successfully.');
    }
}
