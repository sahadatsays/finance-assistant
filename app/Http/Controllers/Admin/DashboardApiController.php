<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    public function __construct(
        private AdminDashboardService $dashboard,
    ) {}

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'metrics' => $this->dashboard->metrics(),
            'charts' => $this->dashboard->charts(),
        ]);
    }
}
