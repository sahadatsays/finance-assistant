<?php

namespace App\Services\Platform\Website;

use App\Models\Platform\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadService
{
    public function store(UploadedFile $file, ?User $uploader = null): MediaAsset
    {
        $disk = 'public';
        $path = $file->store('website/'.date('Y/m'), $disk);

        return MediaAsset::query()->create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'alt_text' => Str::of($file->getClientOriginalName())->beforeLast('.')->headline(),
            'uploaded_by' => $uploader?->id,
        ]);
    }
}
