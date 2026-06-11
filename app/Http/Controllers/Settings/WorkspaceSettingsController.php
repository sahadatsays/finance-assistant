<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateWorkspaceSettingsRequest;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Services\TenantService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceSettingsController extends Controller
{
    public function __construct(
        private TenantContextService $tenantContext,
        private TenantService $tenants,
    ) {}

    public function edit(Request $request): Response
    {
        $tenant = $this->resolveTenant($request);

        $this->authorize('update', $tenant);

        return Inertia::render('settings/workspace', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'settings' => $tenant->settings ?? [],
            ],
            'currencies' => config('currencies'),
        ]);
    }

    public function update(UpdateWorkspaceSettingsRequest $request): RedirectResponse
    {
        $tenant = $this->resolveTenant($request);

        $this->authorize('update', $tenant);

        $validated = $request->validated();

        if (isset($validated['settings'])) {
            $this->tenants->updateSettings($tenant, $validated['settings']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Workspace settings updated.')]);

        return to_route('workspace.edit');
    }

    private function resolveTenant(Request $request): Tenant
    {
        $user = $request->user();
        $tenant = $this->tenantContext->resolveForUser($user, $request);

        abort_if($tenant === null, 404);

        return $tenant;
    }
}
