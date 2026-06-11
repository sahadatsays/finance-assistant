<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Contracts\AttachmentStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentStorageService implements AttachmentStorage
{
    public function disk(): string
    {
        return (string) config('api.attachments.disk', 'local');
    }

    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, $this->disk());
    }

    public function move(string $from, string $to): bool
    {
        return Storage::disk($this->disk())->move($from, $to);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk())->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk())->exists($path);
    }

    public function download(string $path, string $name, ?string $mimeType = null): StreamedResponse
    {
        return Storage::disk($this->disk())->download($path, $name, $mimeType !== null ? [
            'Content-Type' => $mimeType,
        ] : []);
    }
}
