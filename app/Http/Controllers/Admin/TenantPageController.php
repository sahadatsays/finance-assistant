<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Http\Requests\Admin\StoreTenantRequest;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Tenant\Services\SubscriptionService;
use App\Modules\Tenant\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantPageController extends Controller
{
    public function __construct(
        private TenantService $tenants,
        private SubscriptionService $subscriptions,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = $this->tenants->listForAdmin(
            $request->only(['status', 'search']),
            (int) $request->integer('per_page', 15),
        );

        return Inertia::render('admin/tenants/index', [
            'tenants' => TenantResource::collection($tenants),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
            'filters' => $request->only(['status', 'search']),
            'plans' => Plan::query()->where('is_active', true)->orderBy('price_monthly')->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $this->tenants->create($request->validated(), $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tenant created successfully.')]);

        return to_route('admin.tenants.index');
    }

    public function suspend(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('suspend', $tenant);

        $this->tenants->suspend($tenant, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tenant suspended.')]);

        return back();
    }

    public function activate(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('activate', $tenant);

        $tenant = $this->tenants->activate($tenant, $request->user());

        if ($tenant->subscription !== null) {
            $this->subscriptions->activate($tenant->subscription);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Tenant activated.')]);

        return back();
    }
}
