<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\FlashesToastMessages;
use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreAccountRequest;
use App\Http\Requests\Finance\UpdateAccountRequest;
use App\Models\Finance\Account;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\AccountType;
use App\Modules\Finance\Resources\AccountResource;
use App\Modules\Finance\Services\AccountService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class AccountController extends Controller
{
    use FlashesToastMessages;
    use ResolvesTenantContext;

    public function __construct(
        private AccountService $accounts,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Account::class, $tenant]);

        $accountList = $this->accounts->listForTenant($tenant);
        $resolvedAccounts = AccountResource::collection($accountList)->resolve();

        $byCurrency = collect($resolvedAccounts)
            ->groupBy('currency')
            ->map(fn ($group, string $currency) => [
                'currency' => $currency,
                'total_balance' => (float) $group->sum('balance'),
                'account_count' => $group->count(),
            ])
            ->sortBy('currency')
            ->values()
            ->all();

        return Inertia::render('accounts/index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'accounts' => $resolvedAccounts,
            'summary' => [
                'account_count' => count($resolvedAccounts),
                'by_currency' => $byCurrency,
            ],
            'accountTypes' => collect(AccountType::cases())->map(fn (AccountType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->all(),
            'currencies' => config('currencies'),
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [Account::class, $tenant]);

        try {
            $this->accounts->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Account created successfully.'));

        return redirect()->route('accounts.index');
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertAccountBelongsToTenant($account, $tenant);
        $this->authorize('update', $account);

        try {
            $this->accounts->update($account, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Account updated successfully.'));

        return redirect()->route('accounts.index');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertAccountBelongsToTenant($account, $tenant);
        $this->authorize('delete', $account);

        try {
            $this->accounts->delete($account, $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['account' => $exception->getMessage()]);
        }

        $this->flashSuccess(__('Account archived successfully.'));

        return redirect()->route('accounts.index');
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();
        $canManage = $user->isPlatformAdmin() || $user->isOwnerOf($tenant);

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [Account::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [Account::class, $tenant]),
            'update' => $canManage,
            'delete' => $canManage,
        ];
    }
}
