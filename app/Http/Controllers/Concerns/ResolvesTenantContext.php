<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Finance\Category;
use App\Models\Platform\Tenant;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\Request;

trait ResolvesTenantContext
{
    protected function resolveTenant(Request $request, TenantContextService $tenantContext): Tenant
    {
        $tenant = $tenantContext->resolveForUser($request->user(), $request);

        if ($tenant === null) {
            abort(403, 'No workspace selected.');
        }

        return $tenant;
    }

    protected function assertCategoryBelongsToTenant(Category $category, Tenant $tenant): void
    {
        if ($category->tenant_id !== $tenant->id) {
            abort(404);
        }
    }
}
