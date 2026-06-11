<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMember
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');

        if (! $tenant instanceof Tenant) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        if ($user === null || ! $user->belongsToTenant($tenant)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not a member of this tenant.');
        }

        if (! $tenant->isAccessible() && ! $user->isPlatformAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'This tenant is not currently accessible.');
        }

        return $next($request);
    }
}
