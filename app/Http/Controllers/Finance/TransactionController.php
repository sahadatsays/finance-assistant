<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Concerns\FlashesToastMessages;
use App\Http\Controllers\Concerns\ResolvesTenantContext;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreTransactionRequest;
use App\Http\Requests\Finance\UpdateTransactionRequest;
use App\Models\Finance\Account;
use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Modules\Finance\Resources\TransactionResource;
use App\Modules\Finance\Services\CategoryService;
use App\Modules\Finance\Services\TagService;
use App\Modules\Finance\Services\TransactionExportService;
use App\Modules\Finance\Services\TransactionService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    use FlashesToastMessages;
    use ResolvesTenantContext;

    public function __construct(
        private TransactionService $transactions,
        private TransactionExportService $export,
        private CategoryService $categories,
        private TagService $tags,
        private TenantContextService $tenantContext,
    ) {}

    public function index(Request $request): Response
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('viewAny', [Transaction::class, $tenant]);

        $this->categories->ensureSystemCategories($tenant);

        $filters = $request->only(['search', 'type', 'category_id', 'account_id', 'tag_id', 'date_from', 'date_to']);
        $paginator = $this->transactions->paginate($tenant, $filters);

        return Inertia::render('transactions/index', [
            'tenant' => ['id' => $tenant->id, 'name' => $tenant->name],
            'transactions' => TransactionResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'filters' => $filters,
            'accounts' => Account::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'type']),
            'categories' => Category::query()
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'color']),
            'tags' => $this->tags->listForTenant($tenant)->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
            ]),
            'permissions' => $this->permissionMap($request, $tenant),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('create', [Transaction::class, $tenant]);

        try {
            $this->transactions->create(
                $tenant,
                $this->payloadFromRequest($request),
                $request->user(),
                $request->file('attachment'),
            );
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['transaction' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Transaction created successfully.'));

        return redirect()->route('transactions.index');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertTransactionBelongsToTenant($transaction, $tenant);
        $this->authorize('update', $transaction);

        try {
            $this->transactions->update(
                $transaction,
                $this->payloadFromRequest($request, partial: true),
                $request->user(),
                $request->file('attachment'),
            );
        } catch (InvalidArgumentException $exception) {
            $this->flashError($exception->getMessage());

            return back()->withErrors(['transaction' => $exception->getMessage()])->withInput();
        }

        $this->flashSuccess(__('Transaction updated successfully.'));

        return redirect()->route('transactions.index');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->assertTransactionBelongsToTenant($transaction, $tenant);
        $this->authorize('delete', $transaction);

        $this->transactions->delete($transaction, $request->user());

        $this->flashSuccess(__('Transaction deleted successfully.'));

        return redirect()->route('transactions.index');
    }

    public function export(Request $request): StreamedResponse
    {
        $tenant = $this->resolveTenant($request, $this->tenantContext);
        $this->authorize('export', [Transaction::class, $tenant]);

        $filters = $request->only(['search', 'type', 'category_id', 'account_id', 'tag_id', 'date_from', 'date_to']);

        return $this->export->exportCsv($tenant, $filters);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request, bool $partial = false): array
    {
        $data = $partial ? $request->only([
            'type', 'account_id', 'transfer_account_id', 'category_id',
            'amount', 'occurred_at', 'notes', 'tags',
        ]) : $request->validated();

        if (isset($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = array_filter(array_map('trim', explode(',', $data['tags'])));
        }

        return $data;
    }

    /**
     * @return array{view: bool, create: bool, update: bool, delete: bool, export: bool}
     */
    private function permissionMap(Request $request, Tenant $tenant): array
    {
        $user = $request->user();

        return [
            'view' => Gate::forUser($user)->allows('viewAny', [Transaction::class, $tenant]),
            'create' => Gate::forUser($user)->allows('create', [Transaction::class, $tenant]),
            'update' => Gate::forUser($user)->allows('create', [Transaction::class, $tenant]),
            'delete' => Gate::forUser($user)->allows('create', [Transaction::class, $tenant]),
            'export' => Gate::forUser($user)->allows('export', [Transaction::class, $tenant]),
        ];
    }
}
