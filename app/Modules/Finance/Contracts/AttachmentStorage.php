<?php

namespace App\Modules\Finance\Contracts;

use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface AttachmentStorage
{
    public function store(UploadedFile $file, string $directory): string;

    public function move(string $from, string $to): bool;

    public function delete(string $path): bool;

    public function exists(string $path): bool;

    public function download(string $path, string $name, ?string $mimeType = null): StreamedResponse;
}
