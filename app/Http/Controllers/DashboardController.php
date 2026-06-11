<?php

namespace App\Http\Controllers;

use App\Services\Finance\TenantDashboardService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private TenantContextService $tenantContext,
        private TenantDashboardService $dashboard,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $tenant = $this->tenantContext->resolveForUser($user, $request);
        $tenants = $this->tenantContext->accessibleTenants($user);

        if ($tenant === null) {
            return Inertia::render('dashboard', [
                'tenant' => null,
                'tenants' => $tenants->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])->values()->all(),
                'metrics' => null,
                'charts' => null,
                'widgets' => null,
            ]);
        }

        return Inertia::render('dashboard', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'tenants' => $tenants->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ])->values()->all(),
            'metrics' => $this->dashboard->metrics($tenant),
            'charts' => $this->dashboard->charts($tenant),
            'widgets' => $this->dashboard->widgets($tenant),
        ]);
    }
}
