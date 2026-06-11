<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreGoalContributionRequest;
use App\Http\Requests\Finance\StoreGoalRequest;
use App\Http\Requests\Finance\UpdateGoalRequest;
use App\Models\Finance\Goal;
use App\Models\Finance\GoalContribution;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\GoalType;
use App\Modules\Finance\Resources\GoalResource;
use App\Modules\Finance\Services\GoalAnalyticsService;
use App\Modules\Finance\Services\GoalExportService;
use App\Modules\Finance\Services\GoalService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoalController extends Controller
{
    use ResolvesTenantContext;

    public function __construct(
        private GoalService $goals,
        private GoalAnalyticsService $analytics,
        private GoalExportService $export,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Goal::class, $tenant]);

        $goalList = $this->goals->listForTenant($tenant);

        return Inertia::render('goals/index', [
            'tenant' => ['id' => $tenant->id, 'name' => $tenant->name],
            'analytics' => $this->analytics->dashboard($tenant),
            'goals' => GoalResource::collection($goalList)->resolve(),
            'goalTypes' => collect(GoalType::cases())->map(fn (GoalType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'color' => $type->defaultColor(),
            ])->all(),
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreGoalRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [Goal::class, $tenant]);

        try {
            $this->goals->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['goal' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('goals.index');
    }

    public function update(UpdateGoalRequest $request, Goal $goal): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertGoalBelongsToTenant($goal, $tenant);
        $this->authorize('update', $goal);

        $this->goals->update($goal, $request->validated(), $request->user());

        return redirect()->route('goals.index');
    }

    public function destroy(Request $request, Goal $goal): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertGoalBelongsToTenant($goal, $tenant);
        $this->authorize('delete', $goal);

        $this->goals->delete($goal, $request->user());

        return redirect()->route('goals.index');
    }

    public function contribute(StoreGoalContributionRequest $request, Goal $goal): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertGoalBelongsToTenant($goal, $tenant);
        $this->authorize('contribute', $goal);

        try {
            $this->goals->addContribution($goal, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['contribution' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('goals.index');
    }

    public function destroyContribution(Request $request, Goal $goal, GoalContribution $contribution): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertGoalBelongsToTenant($goal, $tenant);

        if ($contribution->goal_id !== $goal->id) {
            abort(404);
        }

        $this->authorize('contribute', $goal);

        $this->goals->deleteContribution($contribution, $request->user());

        return redirect()->route('goals.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('export', [Goal::class, $tenant]);

        return $this->export->exportCsv($tenant);
    }

    protected function assertGoalBelongsToTenant(Goal $goal, Tenant $tenant): void
    {
        if ($goal->tenant_id !== $tenant->id) {
            abort(404);
        }
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool, contribute: bool, export: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();
        $canManage = $user->isPlatformAdmin() || $user->isOwnerOf($tenant);

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [Goal::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [Goal::class, $tenant]),
            'update' => $canManage,
            'delete' => $canManage,
            'contribute' => $user->isPlatformAdmin() || $user->belongsToTenant($tenant),
            'export' => Gate::forUser($user)->allows('export', [Goal::class, $tenant]),
        ];
    }
}
