<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Http\Requests\UpdateTenantSettingsRequest;
use App\Modules\Tenant\Resources\TenantResource;
use App\Modules\Tenant\Services\TenantService;
use Illuminate\Http\JsonResponse;

class TenantSettingsController extends Controller
{
    public function __construct(
        private TenantService $tenants,
    ) {}

    public function show(Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);

        $tenant->load(['subscription.plan']);

        return response()->json([
            'tenant' => new TenantResource($tenant),
        ]);
    }

    public function update(UpdateTenantSettingsRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('update', $tenant);

        $validated = $request->validated();

        if (isset($validated['name'])) {
            $tenant = $this->tenants->update($tenant, ['name' => $validated['name']]);
        }

        if (isset($validated['settings'])) {
            $tenant = $this->tenants->updateSettings($tenant, $validated['settings']);
        }

        return response()->json([
            'message' => 'Tenant settings updated successfully.',
            'tenant' => new TenantResource($tenant->load(['subscription.plan'])),
        ]);
    }
}
