<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\ActivityLogService;
use App\Services\Platform\PlatformSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemSettingsController extends Controller
{
    public function __construct(
        private PlatformSettingService $settings,
        private ActivityLogService $activityLog,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/settings/index', [
            'settings' => $this->settings->grouped(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'maintenance_mode' => ['boolean'],
            'allow_registration' => ['boolean'],
        ]);

        $this->settings->updateMany($validated);

        $this->activityLog->log(
            'Platform settings were updated.',
            causer: $request->user(),
            properties: $validated,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Settings saved.')]);

        return to_route('admin.settings.index');
    }
}
