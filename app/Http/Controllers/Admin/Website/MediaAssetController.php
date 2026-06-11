<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\MediaAsset;
use App\Services\Platform\ActivityLogService;
use App\Services\Platform\Website\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaAssetController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private MediaUploadService $uploader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/media/index', [
            'assets' => MediaAsset::query()
                ->latest()
                ->paginate(24)
                ->through(fn (MediaAsset $asset) => [
                    'id' => $asset->id,
                    'filename' => $asset->filename,
                    'mime_type' => $asset->mime_type,
                    'size' => $asset->size,
                    'alt_text' => $asset->alt_text,
                    'url' => $asset->url(),
                    'created_at' => $asset->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $asset = $this->uploader->store($validated['file'], $request->user());

        if (! empty($validated['alt_text'])) {
            $asset->update(['alt_text' => $validated['alt_text']]);
        }

        $this->activityLog->log("Media asset \"{$asset->filename}\" was uploaded.", subject: $asset, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Media uploaded.')]);

        return to_route('admin.website.media.index');
    }

    public function destroy(Request $request, MediaAsset $mediaAsset): RedirectResponse
    {
        $filename = $mediaAsset->filename;
        $mediaAsset->delete();

        $this->activityLog->log("Media asset \"{$filename}\" was deleted.", causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Media deleted.')]);

        return to_route('admin.website.media.index');
    }
}
