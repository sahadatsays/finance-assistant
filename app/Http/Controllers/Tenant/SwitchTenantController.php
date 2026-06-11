<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchTenantController extends Controller
{
    public function __invoke(Request $request, Tenant $tenant, TenantContextService $tenantContext): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->belongsToTenant($tenant)) {
            abort(403);
        }

        if (! $tenant->isAccessible() && ! $user->isPlatformAdmin()) {
            abort(403, 'This tenant is not currently accessible.');
        }

        $tenantContext->setCurrent($tenant, $request);

        return redirect()->route('dashboard');
    }
}
