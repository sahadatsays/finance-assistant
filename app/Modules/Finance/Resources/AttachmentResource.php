<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Attachment;
use App\Modules\Finance\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attachment
 */
class AttachmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $signed = app(AttachmentService::class)->signedDownloadUrl($this->resource);

        return [
            'id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $signed['url'],
            'url_expires_at' => $signed['expires_at'],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
