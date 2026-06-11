<?php

namespace App\Http\Requests\Api\Attachment;

use App\Http\Requests\Api\ApiFormRequest;

class StoreUploadRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ];
    }
}
