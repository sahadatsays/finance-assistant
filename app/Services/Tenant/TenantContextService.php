<?php

namespace App\Services\Tenant;

use App\Models\Platform\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TenantContextService
{
    private const string SESSION_KEY = 'current_tenant_id';

    /**
     * @return Collection<int, Tenant>
     */
    public function accessibleTenants(User $user): Collection
    {
        return $user->tenants()
            ->where(function ($query) use ($user): void {
                if (! $user->isPlatformAdmin()) {
                    $query->whereIn('status', ['trial', 'active']);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function resolveForUser(User $user, Request $request): ?Tenant
    {
        $tenants = $this->accessibleTenants($user);

        if ($tenants->isEmpty()) {
            return null;
        }

        $requestedTenantId = $this->resolveRequestedTenantId($request);

        if ($requestedTenantId !== null) {
            $tenant = $tenants->firstWhere('id', $requestedTenantId);

            if ($tenant !== null) {
                if ($request->hasSession()) {
                    $this->setCurrent($tenant, $request);
                }

                return $tenant;
            }
        }

        if ($request->hasSession()) {
            $sessionTenantId = $request->session()->get(self::SESSION_KEY);

            if ($sessionTenantId !== null) {
                $tenant = $tenants->firstWhere('id', $sessionTenantId);

                if ($tenant !== null) {
                    return $tenant;
                }
            }
        }

        $tenant = $tenants->first();

        if ($tenant !== null && $request->hasSession()) {
            $this->setCurrent($tenant, $request);
        }

        return $tenant;
    }

    public function setCurrent(Tenant $tenant, Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $tenant->id);
    }

    public function current(Request $request): ?Tenant
    {
        if (! $request->hasSession()) {
            return null;
        }

        $tenantId = $request->session()->get(self::SESSION_KEY);

        if ($tenantId === null) {
            return null;
        }

        return Tenant::query()->find($tenantId);
    }

    private function resolveRequestedTenantId(Request $request): ?int
    {
        $header = $request->header('X-Tenant-Id');

        if (is_string($header) && $header !== '' && ctype_digit($header)) {
            return (int) $header;
        }

        $query = $request->query('tenant_id');

        if (is_string($query) && $query !== '' && ctype_digit($query)) {
            return (int) $query;
        }

        if (is_int($query)) {
            return $query;
        }

        return null;
    }
}
