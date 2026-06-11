<?php

namespace App\Http\Middleware;

use App\Models\Platform\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwner
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

        if ($user === null || (! $user->isOwnerOf($tenant) && ! $user->isPlatformAdmin())) {
            abort(Response::HTTP_FORBIDDEN, 'Tenant owner access required.');
        }

        return $next($request);
    }
}
