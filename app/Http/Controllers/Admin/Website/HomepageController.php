<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\MediaAsset;
use App\Services\Platform\ActivityLogService;
use App\Services\Platform\Website\WebsiteContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomepageController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private WebsiteContentService $content,
    ) {}

    public function index(): Response
    {
        $homepage = $this->content->homepage()->load('heroImage');

        return Inertia::render('admin/website/homepage/index', [
            'homepage' => [
                'id' => $homepage->id,
                'hero_eyebrow' => $homepage->hero_eyebrow,
                'hero_title' => $homepage->hero_title,
                'hero_subtitle' => $homepage->hero_subtitle,
                'hero_primary_label' => $homepage->hero_primary_label,
                'hero_primary_url' => $homepage->hero_primary_url,
                'hero_secondary_label' => $homepage->hero_secondary_label,
                'hero_secondary_url' => $homepage->hero_secondary_url,
                'hero_image_id' => $homepage->hero_image_id,
                'hero_image_url' => $homepage->heroImage?->url(),
                'statistics' => $homepage->statistics ?? [],
                'features' => $homepage->features ?? [],
                'cta_sections' => $homepage->cta_sections ?? [],
                'is_active' => $homepage->is_active,
            ],
            'media' => MediaAsset::query()->latest()->limit(30)->get()->map(fn (MediaAsset $m) => [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => $m->url(),
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        foreach (['statistics', 'features', 'cta_sections'] as $field) {
            if ($request->has($field) && is_string($request->input($field))) {
                $request->merge([
                    $field => json_decode($request->input($field), true) ?? [],
                ]);
            }
        }

        $validated = $request->validate([
            'hero_eyebrow' => ['nullable', 'string', 'max:255'],
            'hero_title' => ['nullable', 'string', 'max:500'],
            'hero_subtitle' => ['nullable', 'string', 'max:2000'],
            'hero_primary_label' => ['nullable', 'string', 'max:100'],
            'hero_primary_url' => ['nullable', 'string', 'max:500'],
            'hero_secondary_label' => ['nullable', 'string', 'max:100'],
            'hero_secondary_url' => ['nullable', 'string', 'max:500'],
            'hero_image_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'statistics' => ['nullable', 'array'],
            'statistics.*.label' => ['required_with:statistics', 'string', 'max:100'],
            'statistics.*.value' => ['required_with:statistics', 'string', 'max:100'],
            'features' => ['nullable', 'array'],
            'features.*.title' => ['required_with:features', 'string', 'max:255'],
            'features.*.description' => ['required_with:features', 'string', 'max:500'],
            'cta_sections' => ['nullable', 'array'],
            'cta_sections.*.title' => ['required_with:cta_sections', 'string', 'max:255'],
            'cta_sections.*.subtitle' => ['nullable', 'string', 'max:500'],
            'cta_sections.*.primary_label' => ['nullable', 'string', 'max:100'],
            'cta_sections.*.primary_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $homepage = $this->content->homepage();
        $homepage->update($validated);

        $this->activityLog->log('Homepage content was updated.', subject: $homepage, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Homepage updated.')]);

        return to_route('admin.website.homepage.index');
    }
}
