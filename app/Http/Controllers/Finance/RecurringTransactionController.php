<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\FlashesToastMessages;
use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreRecurringTransactionRequest;
use App\Http\Requests\Finance\UpdateRecurringTransactionRequest;
use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\RecurringTransaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Enums\RecurrenceFrequency;
use App\Modules\Finance\Resources\RecurringTransactionResource;
use App\Modules\Finance\Services\CategoryService;
use App\Modules\Finance\Services\RecurringTransactionService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

class RecurringTransactionController extends Controller
{
    use FlashesToastMessages;
    use ResolvesTenantContext;

    public function __construct(
        private RecurringTransactionService $recurringTransactions,
        private CategoryService $categories,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [RecurringTransaction::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $rules = RecurringTransaction::query()
            ->with(['account', 'category'])
            ->withCount('transactions')
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('is_active')
            ->orderBy('next_run_at')
            ->get();

        return Inertia::render('recurring-transactions/index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'recurringTransactions' => RecurringTransactionResource::collection($rules)->resolve(),
            'accounts' => Account::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'currency']),
            'categories' => Category::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'color']),
            'frequencies' => collect(RecurrenceFrequency::cases())->map(fn (RecurrenceFrequency $frequency) => [
                'value' => $frequency->value,
                'label' => $frequency->label(),
            ])->all(),
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreRecurringTransactionRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [RecurringTransaction::class, $tenant]);

        try {
            $this->recurringTransactions->create($tenant, $request->validated(), $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Scheduled transaction created successfully.'));

        return redirect()->route('recurring-transactions.index');
    }

    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertRecurringTransactionBelongsToTenant($recurringTransaction, $tenant);
        $this->authorize('update', $recurringTransaction);

        try {
            $this->recurringTransactions->update(
                $recurringTransaction,
                $request->validated(),
                $request->user(),
            );
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['name' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Scheduled transaction updated successfully.'));

        return redirect()->route('recurring-transactions.index');
    }

    public function destroy(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertRecurringTransactionBelongsToTenant($recurringTransaction, $tenant);
        $this->authorize('delete', $recurringTransaction);

        $this->recurringTransactions->delete($recurringTransaction, $request->user());

        $this->flashSuccess(__('Scheduled transaction paused successfully.'));

        return redirect()->route('recurring-transactions.index');
    }

    public function resume(Request $request, RecurringTransaction $recurringTransaction): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertRecurringTransactionBelongsToTenant($recurringTransaction, $tenant);
        $this->authorize('update', $recurringTransaction);

        try {
            $this->recurringTransactions->resume($recurringTransaction, $request->user());
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['recurring_transaction' => $exception->getMessage()]);
        }

        $this->flashSuccess(__('Scheduled transaction resumed successfully.'));

        return redirect()->route('recurring-transactions.index');
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [RecurringTransaction::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [RecurringTransaction::class, $tenant]),
            'update' => Gate::forUser($user)->allows('create', [RecurringTransaction::class, $tenant]),
            'delete' => Gate::forUser($user)->allows('create', [RecurringTransaction::class, $tenant]),
        ];
    }

    private function assertRecurringTransactionBelongsToTenant(
        RecurringTransaction $recurringTransaction,
        Tenant $tenant,
    ): void {
        if ($recurringTransaction->tenant_id !== $tenant->id) {
            abort(404);
        }
    }
}
