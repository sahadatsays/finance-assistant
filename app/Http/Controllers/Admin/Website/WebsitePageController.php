<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\Website\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Platform\WebsitePage;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WebsitePageController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/pages/index', [
            'pages' => WebsitePage::query()->orderBy('sort_order')->get(),
            'statuses' => array_column(ContentStatus::cases(), 'value'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(WebsitePage::class)],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $page = WebsitePage::query()->create([
            ...$validated,
            'published_at' => $validated['status'] === ContentStatus::Published->value ? now() : null,
        ]);

        $this->activityLog->log("Website page \"{$page->title}\" was created.", subject: $page, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page created.')]);

        return to_route('admin.website.pages.index');
    }

    public function update(Request $request, WebsitePage $websitePage): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique(WebsitePage::class)->ignore($websitePage->id)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(ContentStatus::class)],
            'sort_order' => ['integer', 'min:0'],
        ]);

        if (isset($validated['status']) && $validated['status'] === ContentStatus::Published->value && $websitePage->published_at === null) {
            $validated['published_at'] = now();
        }

        $websitePage->update($validated);

        $this->activityLog->log("Website page \"{$websitePage->title}\" was updated.", subject: $websitePage, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page updated.')]);

        return to_route('admin.website.pages.index');
    }

    public function destroy(Request $request, WebsitePage $websitePage): RedirectResponse
    {
        $title = $websitePage->title;
        $websitePage->delete();

        $this->activityLog->log("Website page \"{$title}\" was deleted.", causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Page deleted.')]);

        return to_route('admin.website.pages.index');
    }
}
