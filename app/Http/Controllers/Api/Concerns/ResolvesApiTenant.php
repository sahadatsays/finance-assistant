<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Platform\Tenant;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

trait ResolvesApiTenant
{
    protected function resolveApiTenant(Request $request, TenantContextService $tenantContext): Tenant
    {
        $user = $request->user();
        $tenant = $tenantContext->resolveForUser($user, $request);

        if ($tenant === null) {
            throw new HttpResponseException($this->error('No workspace available.', 403));
        }

        if (! $tenant->isAccessible() && ! $user->isPlatformAdmin()) {
            throw new HttpResponseException($this->error('This workspace is not currently accessible.', 403));
        }

        if (! $user->isPlatformAdmin() && ! $user->belongsToTenant($tenant)) {
            throw new HttpResponseException($this->error('You are not a member of this workspace.', 403));
        }

        return $tenant;
    }
}
