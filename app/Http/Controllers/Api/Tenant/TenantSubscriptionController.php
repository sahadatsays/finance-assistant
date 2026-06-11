<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Modules\Tenant\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;

class TenantSubscriptionController extends Controller
{
    public function show(Tenant $tenant): JsonResponse
    {
        $this->authorize('view', $tenant);

        $tenant->load('subscription.plan');

        return response()->json([
            'subscription' => new SubscriptionResource($tenant->subscription),
        ]);
    }
}
