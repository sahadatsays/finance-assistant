<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\FlashesToastMessages;
use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCategoryRequest;
use App\Http\Requests\Finance\UpdateCategoryRequest;
use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Resources\CategoryResource;
use App\Modules\Finance\Services\CategoryService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class CategoryController extends Controller
{
    use FlashesToastMessages;
    use ResolvesTenantContext;

    public function __construct(
        private CategoryService $categories,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Category::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $includeArchived = $request->boolean('archived');
        $type = $request->query('type', 'income');
        $categories = $includeArchived
            ? $this->categories->listArchivedForTenant($tenant)
            : $this->categories->listForTenant($tenant);

        return Inertia::render('categories/index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'categories' => CategoryResource::collection($categories)->resolve(),
            'filters' => [
                'archived' => $includeArchived,
                'type' => in_array($type, ['income', 'expense'], true) ? $type : 'income',
            ],
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [Category::class, $tenant]);

        try {
            $this->categories->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Category created successfully.'));

        return redirect()->route('categories.index', [
            'type' => $request->validated('type'),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertCategoryBelongsToTenant($category, $tenant);
        $this->authorize('update', $category);

        try {
            $this->categories->update($category, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Category updated successfully.'));

        return redirect()->route('categories.index', [
            'type' => $category->type->value,
        ]);
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertCategoryBelongsToTenant($category, $tenant);
        $this->authorize('delete', $category);

        try {
            $this->categories->delete($category, $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['category' => $exception->getMessage()]);
        }

        $this->flashSuccess(__('Category deleted successfully.'));

        return redirect()->route('categories.index', [
            'type' => $this->resolveRedirectType($request, $category),
        ]);
    }

    public function archive(Request $request, Category $category): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertCategoryBelongsToTenant($category, $tenant);
        $this->authorize('archive', $category);

        $this->categories->archive($category, $request->user());

        $this->flashSuccess(__('Category archived successfully.'));

        return redirect()->route('categories.index', [
            'type' => $this->resolveRedirectType($request, $category),
        ]);
    }

    public function restore(Request $request, Category $category): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertCategoryBelongsToTenant($category, $tenant);
        $this->authorize('restore', $category);

        $this->categories->restore($category, $request->user());

        $this->flashSuccess(__('Category restored successfully.'));

        return redirect()->route('categories.index', ['archived' => 1]);
    }

    private function resolveRedirectType(Request $request, Category $category): string
    {
        $type = $request->input('type', $category->type->value);

        return in_array($type, ['income', 'expense'], true) ? $type : $category->type->value;
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool, archive: bool, restore: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();
        $canManage = $user->isPlatformAdmin() || $user->isOwnerOf($tenant);

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [Category::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [Category::class, $tenant]),
            'update' => $canManage,
            'delete' => $canManage,
            'archive' => $canManage,
            'restore' => $canManage,
        ];
    }
}
