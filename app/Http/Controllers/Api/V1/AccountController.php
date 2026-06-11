<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Account\StoreAccountRequest;
use App\Http\Requests\Api\Account\UpdateAccountRequest;
use App\Models\Finance\Account;
use App\Modules\Finance\Resources\AccountResource;
use App\Modules\Finance\Services\AccountService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AccountController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private AccountService $accounts,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Account::class, $tenant]);

        return $this->success(
            data: ['accounts' => AccountResource::collection($this->accounts->listForTenant($tenant))],
            message: 'Accounts retrieved successfully.',
        );
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $this->authorize('create', [Account::class, $tenant]);

        $account = $this->accounts->create($tenant, $request->validated(), $request->user());

        return $this->success(
            data: ['account' => new AccountResource($account)],
            message: 'Account created successfully.',
            status: 201,
        );
    }

    public function update(UpdateAccountRequest $request, int $account): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->accounts->findForTenant($tenant, $account);

        if ($model === null) {
            return $this->error('Account not found.', 404);
        }

        $this->authorize('update', $model);

        $model = $this->accounts->update($model, $request->validated(), $request->user());

        return $this->success(
            data: ['account' => new AccountResource($model)],
            message: 'Account updated successfully.',
        );
    }

    public function destroy(Request $request, int $account): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $model = $this->accounts->findForTenant($tenant, $account);

        if ($model === null) {
            return $this->error('Account not found.', 404);
        }

        $this->authorize('delete', $model);

        try {
            $this->accounts->delete($model, $request->user());
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 422);
        }

        return $this->success(message: 'Account deleted successfully.');
    }
}
