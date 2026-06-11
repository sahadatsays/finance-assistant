<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreBudgetRequest;
use App\Http\Requests\Finance\UpdateBudgetRequest;
use App\Models\Finance\Budget;
use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Resources\BudgetResource;
use App\Modules\Finance\Services\BudgetAnalyticsService;
use App\Modules\Finance\Services\BudgetExportService;
use App\Modules\Finance\Services\BudgetService;
use App\Modules\Finance\Services\CategoryService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetController extends Controller
{
    use ResolvesTenantContext;

    public function __construct(
        private BudgetService $budgets,
        private BudgetAnalyticsService $analytics,
        private BudgetExportService $export,
        private CategoryService $categories,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Budget::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $budgetList = $this->budgets->listForTenant($tenant);

        return Inertia::render('budgets/index', [
            'tenant' => ['id' => $tenant->id, 'name' => $tenant->name],
            'analytics' => $this->analytics->dashboard($tenant),
            'budgets' => BudgetResource::collection($budgetList)->resolve(),
            'expenseCategories' => Category::query()
                ->where('tenant_id', $tenant->id)
                ->where('type', CategoryType::Expense)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'color']),
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [Budget::class, $tenant]);

        try {
            $this->budgets->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['budget' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('budgets.index');
    }

    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertBudgetBelongsToTenant($budget, $tenant);
        $this->authorize('update', $budget);

        try {
            $this->budgets->update($budget, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['budget' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('budgets.index');
    }

    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertBudgetBelongsToTenant($budget, $tenant);
        $this->authorize('delete', $budget);

        $this->budgets->delete($budget, $request->user());

        return redirect()->route('budgets.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('export', [Budget::class, $tenant]);

        return $this->export->exportCsv($tenant);
    }

    protected function assertBudgetBelongsToTenant(Budget $budget, Tenant $tenant): void
    {
        if ($budget->tenant_id !== $tenant->id) {
            abort(404);
        }
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool, export: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();
        $canManage = $user->isPlatformAdmin() || $user->isOwnerOf($tenant);

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [Budget::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [Budget::class, $tenant]),
            'update' => $canManage,
            'delete' => $canManage,
            'export' => Gate::forUser($user)->allows('export', [Budget::class, $tenant]),
        ];
    }
}
