<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\Website\NavigationLocation;
use App\Http\Controllers\Controller;
use App\Models\Platform\NavigationItem;
use App\Services\Platform\ActivityLogService;
use App\Services\Platform\Website\WebsiteContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FooterSettingController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private WebsiteContentService $content,
    ) {}

    public function index(): Response
    {
        $footerLocations = [
            NavigationLocation::FooterProduct,
            NavigationLocation::FooterResources,
            NavigationLocation::FooterCompany,
            NavigationLocation::FooterLegal,
        ];

        return Inertia::render('admin/website/footer/index', [
            'settings' => $this->content->footerSettings(),
            'links' => NavigationItem::query()
                ->whereIn('location', $footerLocations)
                ->orderBy('location')
                ->orderBy('sort_order')
                ->get(),
            'locations' => array_map(fn (NavigationLocation $l) => $l->value, $footerLocations),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'copyright_text' => ['nullable', 'string', 'max:500'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'show_newsletter' => ['boolean'],
            'newsletter_heading' => ['nullable', 'string', 'max:255'],
            'trust_badges' => ['nullable', 'array'],
            'trust_badges.*' => ['string', 'max:255'],
        ]);

        $settings = $this->content->footerSettings();
        $settings->update($validated);

        $this->activityLog->log('Footer settings were updated.', subject: $settings, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Footer settings updated.')]);

        return to_route('admin.website.footer.index');
    }

    public function storeLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location' => ['required', Rule::in([
                NavigationLocation::FooterProduct->value,
                NavigationLocation::FooterResources->value,
                NavigationLocation::FooterCompany->value,
                NavigationLocation::FooterLegal->value,
            ])],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        NavigationItem::query()->create($validated);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Footer link created.')]);

        return to_route('admin.website.footer.index');
    }
}
