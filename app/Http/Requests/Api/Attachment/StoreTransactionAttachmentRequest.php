<?php

namespace App\Http\Requests\Api\Attachment;

use App\Http\Requests\Api\ApiFormRequest;

class StoreTransactionAttachmentRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required_without:upload_id', 'nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
            'upload_id' => ['required_without:file', 'nullable', 'uuid'],
        ];
    }
}
