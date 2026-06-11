<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Goal\ContributeGoalRequest;
use App\Http\Requests\Api\Goal\ListGoalsRequest;
use App\Http\Requests\Api\Goal\StoreGoalRequest;
use App\Http\Requests\Api\Goal\UpdateGoalRequest;
use App\Models\Finance\Goal;
use App\Modules\Finance\Resources\GoalResource;
use App\Modules\Finance\Services\GoalService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class GoalController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private GoalService $goals,
    ) {}

    public function index(ListGoalsRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Goal::class, $tenant]);

        $filters = $request->filters();
        $perPage = (int) $request->validated('per_page', 15);

        $paginator = $this->goals->paginateForTenant($tenant, $filters, $perPage);

        return $this->paginated(
            $paginator->through(fn (Goal $goal) => new GoalResource($goal)),
            message: 'Savings goals retrieved successfully.',
            meta: [
                'filters' => array_merge([
                    'type' => null,
                    'search' => null,
                    'sort' => 'target_date',
                    'direction' => 'asc',
                ], $filters),
            ],
        );
    }

    public function show(Request $request, int $goal): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->goals->findForTenant($tenant, $goal);

        if ($model === null) {
            return $this->error('Savings goal not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['goal' => new GoalResource($model)],
            message: 'Savings goal retrieved successfully.',
        );
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Goal::class, $tenant]);

        try {
            $goal = $this->goals->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['goal' => new GoalResource($goal)],
            message: 'Savings goal created successfully.',
            status: 201,
        );
    }

    public function update(UpdateGoalRequest $request, int $goal): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->goals->findForTenant($tenant, $goal);

        if ($model === null) {
            return $this->error('Savings goal not found.', 404);
        }

        $this->authorize('update', $model);

        $model = $this->goals->update($model, $request->validated(), $request->user());

        return $this->success(
            data: ['goal' => new GoalResource($model)],
            message: 'Savings goal updated successfully.',
        );
    }

    public function destroy(Request $request, int $goal): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->goals->findForTenant($tenant, $goal);

        if ($model === null) {
            return $this->error('Savings goal not found.', 404);
        }

        $this->authorize('delete', $model);

        $this->goals->delete($model, $request->user());

        return $this->success(message: 'Savings goal deleted successfully.');
    }

    public function contribute(ContributeGoalRequest $request, int $goal): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->goals->findForTenant($tenant, $goal);

        if ($model === null) {
            return $this->error('Savings goal not found.', 404);
        }

        $this->authorize('contribute', $model);

        try {
            $contribution = $this->goals->addContribution($model, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        $model = $this->goals->findForTenant($tenant, $goal);

        return $this->success(
            data: [
                'contribution' => [
                    'id' => $contribution->id,
                    'amount' => (float) $contribution->amount,
                    'notes' => $contribution->notes,
                    'contributed_at' => $contribution->contributed_at->toIso8601String(),
                ],
                'goal' => new GoalResource($model),
            ],
            message: 'Contribution added successfully.',
            status: 201,
        );
    }
}
