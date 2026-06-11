<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Transaction\ListTransactionsRequest;
use App\Http\Requests\Api\Transaction\StoreTransactionRequest;
use App\Http\Requests\Api\Transaction\UpdateTransactionRequest;
use App\Models\Finance\Transaction;
use App\Modules\Finance\Resources\TransactionResource;
use App\Modules\Finance\Services\TransactionService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TransactionController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private TransactionService $transactions,
    ) {}

    public function index(ListTransactionsRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Transaction::class, $tenant]);

        $filters = $request->filters();
        $perPage = (int) $request->validated('per_page', 15);

        $paginator = $this->transactions->paginate($tenant, $filters, $perPage);

        return $this->paginated(
            $paginator->through(fn (Transaction $transaction) => new TransactionResource($transaction)),
            message: 'Transactions retrieved successfully.',
            meta: [
                'filters' => array_merge([
                    'search' => null,
                    'type' => null,
                    'category_id' => null,
                    'account_id' => null,
                    'tag_id' => null,
                    'date_from' => null,
                    'date_to' => null,
                    'amount_min' => null,
                    'amount_max' => null,
                    'sort' => 'occurred_at',
                    'direction' => 'desc',
                ], $filters),
            ],
        );
    }

    public function show(Request $request, int $transaction): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->transactions->findForTenant($tenant, $transaction);

        if ($model === null) {
            return $this->error('Transaction not found.', 404);
        }

        $this->authorize('view', $model);

        return $this->success(
            data: ['transaction' => new TransactionResource($model)],
            message: 'Transaction retrieved successfully.',
        );
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Transaction::class, $tenant]);

        try {
            $transaction = $this->transactions->create(
                $tenant,
                $request->validated(),
                $request->user(),
                $request->file('attachment'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['transaction' => new TransactionResource($transaction)],
            message: 'Transaction created successfully.',
            status: 201,
        );
    }

    public function update(UpdateTransactionRequest $request, int $transaction): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->transactions->findForTenant($tenant, $transaction);

        if ($model === null) {
            return $this->error('Transaction not found.', 404);
        }

        $this->authorize('update', $model);

        try {
            $model = $this->transactions->update(
                $model,
                $request->validated(),
                $request->user(),
                $request->file('attachment'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(
            data: ['transaction' => new TransactionResource($model)],
            message: 'Transaction updated successfully.',
        );
    }

    public function destroy(Request $request, int $transaction): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);

        $model = $this->transactions->findForTenant($tenant, $transaction);

        if ($model === null) {
            return $this->error('Transaction not found.', 404);
        }

        $this->authorize('delete', $model);

        $this->transactions->delete($model, $request->user());

        return $this->success(message: 'Transaction deleted successfully.');
    }
}
