<?php

namespace App\Http\Requests\Api\Sync;

use App\Http\Requests\Api\ApiFormRequest;

class SyncRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'since' => ['sometimes', 'date'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:500'],
        ];
    }
}
