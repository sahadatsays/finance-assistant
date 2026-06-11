<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\Website\NavigationLocation;
use App\Http\Controllers\Controller;
use App\Models\Platform\NavigationItem;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NavigationItemController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/navigation/index', [
            'items' => NavigationItem::query()->orderBy('location')->orderBy('sort_order')->get(),
            'locations' => array_column(NavigationLocation::cases(), 'value'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location' => ['required', Rule::enum(NavigationLocation::class)],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'opens_in_new_tab' => ['boolean'],
        ]);

        $item = NavigationItem::query()->create($validated);

        $this->activityLog->log("Navigation item \"{$item->label}\" was created.", subject: $item, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Navigation item created.')]);

        return to_route('admin.website.navigation.index');
    }

    public function update(Request $request, NavigationItem $navigationItem): RedirectResponse
    {
        $validated = $request->validate([
            'location' => ['sometimes', 'required', Rule::enum(NavigationLocation::class)],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'opens_in_new_tab' => ['boolean'],
        ]);

        $navigationItem->update($validated);

        $this->activityLog->log("Navigation item \"{$navigationItem->label}\" was updated.", subject: $navigationItem, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Navigation item updated.')]);

        return to_route('admin.website.navigation.index');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:navigation_items,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            NavigationItem::query()->whereKey($item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Navigation order updated.')]);

        return to_route('admin.website.navigation.index');
    }

    public function destroy(Request $request, NavigationItem $navigationItem): RedirectResponse
    {
        $label = $navigationItem->label;
        $navigationItem->delete();

        $this->activityLog->log("Navigation item \"{$label}\" was deleted.", causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Navigation item deleted.')]);

        return to_route('admin.website.navigation.index');
    }
}
