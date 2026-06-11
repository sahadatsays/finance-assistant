<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\MediaAsset;
use App\Models\Platform\SeoEntry;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeoEntryController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        $pageKeys = array_keys(config('marketing.seo', []));

        foreach ($pageKeys as $key) {
            SeoEntry::query()->firstOrCreate(
                ['page_key' => $key],
                [
                    'meta_title' => config("marketing.seo.{$key}.title"),
                    'meta_description' => config("marketing.seo.{$key}.description"),
                ],
            );
        }

        return Inertia::render('admin/website/seo/index', [
            'entries' => SeoEntry::query()->with('ogImage')->orderBy('page_key')->get(),
            'media' => MediaAsset::query()->latest()->limit(50)->get()->map(fn (MediaAsset $m) => [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => $m->url(),
            ]),
            'pageKeys' => $pageKeys,
        ]);
    }

    public function update(Request $request, SeoEntry $seoEntry): RedirectResponse
    {
        if ($request->has('meta_keywords') && is_string($request->input('meta_keywords'))) {
            $keywords = array_values(array_filter(array_map(
                trim(...),
                explode(',', $request->input('meta_keywords')),
            )));
            $request->merge(['meta_keywords' => $keywords]);
        }

        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'array'],
            'meta_keywords.*' => ['string', 'max:100'],
            'og_image_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
        ]);

        $seoEntry->update($validated);

        $this->activityLog->log("SEO entry \"{$seoEntry->page_key}\" was updated.", subject: $seoEntry, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('SEO entry updated.')]);

        return to_route('admin.website.seo.index');
    }
}
