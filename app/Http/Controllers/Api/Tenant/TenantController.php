<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Tenant\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private TenantService $tenants,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenants = $this->tenants->listForUser($request->user());

        return response()->json([
            'tenants' => TenantResource::collection($tenants),
        ]);
    }

    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        $tenant->load(['subscription.plan'])->loadCount('tenantUsers');

        return response()->json([
            'tenant' => new TenantResource($tenant),
        ]);
    }
}
