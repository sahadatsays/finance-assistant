<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use App\Modules\Tenant\Resources\PlanResource;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    public function index(): Response
    {
        $plans = Plan::query()->orderBy('price_monthly')->get();

        return Inertia::render('admin/plans/index', [
            'plans' => PlanResource::collection($plans)->resolve(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(Plan::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'max_users' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $plan = Plan::query()->create($validated);

        $this->activityLog->log(
            "Plan \"{$plan->name}\" was created.",
            subject: $plan,
            causer: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan created.')]);

        return to_route('admin.plans.index');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique(Plan::class)->ignore($plan->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price_monthly' => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_users' => ['sometimes', 'required', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $plan->update($validated);

        $this->activityLog->log(
            "Plan \"{$plan->name}\" was updated.",
            subject: $plan,
            causer: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan updated.')]);

        return to_route('admin.plans.index');
    }
}
