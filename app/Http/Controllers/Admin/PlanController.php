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
        $plans = Plan::query()->orderBy('sort_order')->orderBy('price_monthly')->get();

        return Inertia::render('admin/plans/index', [
            'plans' => PlanResource::collection($plans)->resolve(),
        ]);
    }

    public function websiteIndex(): Response
    {
        $plans = Plan::query()->orderBy('sort_order')->orderBy('price_monthly')->get();

        return Inertia::render('admin/website/plans/index', [
            'plans' => PlanResource::collection($plans)->resolve(),
            'featureLabels' => config('marketing.feature_labels', []),
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
            'sort_order' => ['integer', 'min:0'],
        ]);

        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = (int) Plan::query()->max('sort_order') + 1;
        }

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
            'sort_order' => ['integer', 'min:0'],
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

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plans' => ['required', 'array'],
            'plans.*.id' => ['required', 'integer', 'exists:plans,id'],
            'plans.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['plans'] as $item) {
            Plan::query()->whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        $this->activityLog->log('Subscription plans were reordered.', causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plan order updated.')]);

        return to_route('admin.website.plans.index');
    }
}
