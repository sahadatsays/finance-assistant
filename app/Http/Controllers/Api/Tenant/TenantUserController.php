<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Tenant\Enums\TenantUserRole;
use App\Modules\Tenant\Http\Requests\InviteTenantUserRequest;
use App\Modules\Tenant\Http\Requests\UpdateTenantUserRequest;
use App\Modules\Tenant\Resources\TenantUserResource;
use App\Modules\Tenant\Services\TenantUserService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class TenantUserController extends Controller
{
    public function __construct(
        private TenantUserService $tenantUsers,
    ) {}

    public function index(Tenant $tenant): JsonResponse
    {
        $this->authorize('manageUsers', $tenant);

        return response()->json([
            'users' => TenantUserResource::collection(
                $this->tenantUsers->listForTenant($tenant),
            ),
        ]);
    }

    public function store(InviteTenantUserRequest $request, Tenant $tenant): JsonResponse
    {
        $this->authorize('manageUsers', $tenant);

        try {
            $membership = $this->tenantUsers->invite($tenant, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'User invited successfully.',
            'member' => new TenantUserResource($membership->load('user')),
        ], 201);
    }

    public function update(UpdateTenantUserRequest $request, Tenant $tenant, User $user): JsonResponse
    {
        $this->authorize('manageUsers', $tenant);

        try {
            $membership = $this->tenantUsers->updateRole(
                $tenant,
                $user,
                TenantUserRole::from($request->validated('role')),
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Member role updated successfully.',
            'member' => new TenantUserResource($membership),
        ]);
    }

    public function destroy(Tenant $tenant, User $user): JsonResponse
    {
        $this->authorize('manageUsers', $tenant);

        try {
            $this->tenantUsers->remove($tenant, $user);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }
}
