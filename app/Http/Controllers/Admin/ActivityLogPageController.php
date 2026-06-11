<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogPageController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): Response
    {
        $logs = $this->activityLog->paginate(
            (int) $request->integer('per_page', 20),
            $request->input('log_name'),
        );

        return Inertia::render('admin/activity-logs/index', [
            'logs' => [
                'data' => collect($logs->items())->map(fn ($log) => [
                    'id' => $log->id,
                    'log_name' => $log->log_name,
                    'description' => $log->description,
                    'causer' => $log->causer?->name ?? 'System',
                    'tenant' => $log->tenant?->name,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->toIso8601String(),
                ])->all(),
            ],
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
