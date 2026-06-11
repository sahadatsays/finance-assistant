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
use OpenApi\Attributes as OA;

class CategoryController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private CategoryService $categories,
    ) {}

    #[OA\Get(
        path: '/categories',
        operationId: 'listCategories',
        summary: 'List categories',
        description: 'Returns paginated income and expense categories for the active tenant. Supports filtering by type, kind, archived status, and search.',
        tags: ['Categories'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/XTenantId'),
            new OA\Parameter(ref: '#/components/parameters/Page'),
            new OA\Parameter(ref: '#/components/parameters/PerPage'),
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['income', 'expense'])),
            new OA\Parameter(name: 'kind', in: 'query', schema: new OA\Schema(type: 'string', enum: ['system', 'custom'])),
            new OA\Parameter(name: 'archived', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string', maxLength: 128)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Categories retrieved successfully',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/PaginatedEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/Category'),
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
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

    #[OA\Get(
        path: '/categories/{id}',
        operationId: 'showCategory',
        summary: 'Show category',
        tags: ['Categories'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/XTenantId'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
        ],
    )]
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

    #[OA\Post(
        path: '/categories',
        operationId: 'createCategory',
        summary: 'Create category',
        description: 'Creates a custom category. Tenant owners only.',
        tags: ['Categories'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/XTenantId')],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreCategoryRequest'),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 201),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
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

    #[OA\Put(
        path: '/categories/{id}',
        operationId: 'updateCategory',
        summary: 'Update category',
        tags: ['Categories'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/XTenantId'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/StoreCategoryRequest')),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
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

    #[OA\Delete(
        path: '/categories/{id}',
        operationId: 'deleteCategory',
        summary: 'Delete category',
        description: 'Deletes a custom category. System categories cannot be deleted.',
        tags: ['Categories'],
        security: [['sanctum' => []], ['tenant' => []]],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/XTenantId'),
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
        ],
    )]
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
