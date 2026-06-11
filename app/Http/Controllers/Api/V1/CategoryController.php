<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Category\ListCategoriesRequest;
use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;
use App\Models\Finance\Category;
use App\Modules\Finance\Resources\CategoryResource;
use App\Modules\Finance\Services\CategoryService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CategoryController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private CategoryService $categories,
    ) {}

    public function index(ListCategoriesRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Category::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $filters = $request->filters();
        $perPage = (int) $request->validated('per_page', 15);

        $paginator = $this->categories->paginateForTenant($tenant, $filters, $perPage);

        return $this->paginated(
            $paginator->through(fn (Category $category) => new CategoryResource($category)),
            message: 'Categories retrieved successfully.',
            meta: [
                'filters' => array_merge([
                    'type' => null,
                    'kind' => null,
                    'archived' => false,
                    'search' => null,
                ], $filters),
            ],
        );
    }

    public function show(Request $request, int $category): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->categories->findForTenant($tenant, $category);

        if ($model === null) {
            return $this->error('Category not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['category' => new CategoryResource($model)],
            message: 'Category retrieved successfully.',
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Category::class, $tenant]);

        try {
            $category = $this->categories->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        $category->loadCount('transactions');

        return $this->success(
            data: ['category' => new CategoryResource($category)],
            message: 'Category created successfully.',
            status: 201,
        );
    }

    public function update(UpdateCategoryRequest $request, int $category): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->categories->findForTenant($tenant, $category);

        if ($model === null) {
            return $this->error('Category not found.', 404);
        }

        $this->authorize('update', $model);

        try {
            $model = $this->categories->update($model, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        $model->loadCount('transactions');

        return $this->success(
            data: ['category' => new CategoryResource($model)],
            message: 'Category updated successfully.',
        );
    }

    public function destroy(Request $request, int $category): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->categories->findForTenant($tenant, $category);

        if ($model === null) {
            return $this->error('Category not found.', 404);
        }

        $this->authorize('delete', $model);

        try {
            $this->categories->delete($model, $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(message: 'Category deleted successfully.');
    }
}
