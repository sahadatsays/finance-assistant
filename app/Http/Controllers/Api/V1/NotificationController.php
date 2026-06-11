<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\ResolvesApiTenant;
use App\Http\Requests\Api\Notification\MarkNotificationsReadRequest;
use App\Models\Platform\AppNotification;
use App\Modules\Platform\Services\NotificationService;
use App\Services\Tenant\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends ApiController
{
    use ResolvesApiTenant;

    public function __construct(
        private TenantContextService $tenantContext,
        private NotificationService $notifications,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = $this->resolveApiTenant($request, $this->tenantContext);
        $since = $request->query('since') ? Carbon::parse($request->query('since')) : null;

        $items = $this->notifications->listForUser($tenant, $request->user(), $since);

        return $this->success(
            data: [
                'notifications' => $items->map(fn (AppNotification $n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'data' => $n->data,
                    'read_at' => $n->read_at?->toIso8601String(),
                    'updated_at' => $n->updated_at?->toIso8601String(),
                ]),
            ],
            message: 'Notifications retrieved successfully.',
            meta: ['server_time' => now()->toIso8601String()],
        );
    }

    public function markRead(MarkNotificationsReadRequest $request): JsonResponse
    {
        $count = $this->notifications->markRead(
            $request->user(),
            $request->validated('ids', []),
        );

        return $this->success(
            data: ['marked' => $count],
            message: 'Notifications marked as read.',
        );
    }
}
