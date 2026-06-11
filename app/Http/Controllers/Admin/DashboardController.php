<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\AdminDashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private AdminDashboardService $dashboard,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard', [
            'metrics' => $this->dashboard->metrics(),
            'charts' => $this->dashboard->charts(),
        ]);
    }
}
