<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Http\Requests\Admin\StoreTenantRequest;
use App\Modules\Tenant\Http\Requests\Admin\UpdateTenantSubscriptionRequest;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Tenant\Resources\TenantUsageResource;
use App\Modules\Tenant\Services\SubscriptionService;
use App\Modules\Tenant\Services\TenantService;
use App\Modules\Tenant\Services\TenantUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private TenantService $tenants,
        private SubscriptionService $subscriptions,
        private TenantUsageService $usage,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tenant::class);

        $tenants = $this->tenants->listForAdmin(
            $request->only(['status', 'search']),
            (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => TenantResource::collection($tenants),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $this->authorize('create', Tenant::class);

        $tenant = $this->tenants->create($request->validated(), $request->user());

        return response()->json([
            'message' => 'Tenant created successfully.',
            'tenant' => new TenantResource($tenant),
        ], 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        $tenant->load(['subscription.plan'])->loadCount('tenantUsers');

        return response()->json([
            'tenant' => new TenantResource($tenant),
        ]);
    }

    public function suspend(Tenant $tenant): JsonResponse
    {
        $this->authorize('suspend', $tenant);

        $tenant = $this->tenants->suspend($tenant);

        return response()->json([
            'message' => 'Tenant suspended successfully.',
            'tenant' => new TenantResource($tenant->load(['subscription.plan'])),
        ]);
    }

    public function activate(Tenant $tenant): JsonResponse
    {
        $this->authorize('activate', $tenant);

        $tenant = $this->tenants->activate($tenant);

        if ($tenant->subscription !== null) {
            $this->subscriptions->activate($tenant->subscription);
        }

        return response()->json([
            'message' => 'Tenant activated successfully.',
            'tenant' => new TenantResource($tenant->load(['subscription.plan'])),
        ]);
    }

    public function usage(Tenant $tenant): JsonResponse
    {
        $this->authorize('viewUsage', $tenant);

        return response()->json([
            'tenant_id' => $tenant->id,
            'usage' => new TenantUsageResource($this->usage->getUsage($tenant)),
        ]);
    }

    public function updateSubscription(UpdateTenantSubscriptionRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manageSubscription', $tenant);

        $subscription = $this->subscriptions->changePlan($tenant, $request->validated('plan_id'));

        return response()->json([
            'message' => 'Subscription updated successfully.',
            'tenant' => new TenantResource($tenant->load(['subscription.plan'])),
            'subscription' => $subscription,
        ]);
    }
}
