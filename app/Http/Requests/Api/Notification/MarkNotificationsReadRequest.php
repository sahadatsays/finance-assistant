<?php

namespace App\Http\Requests\Api\Notification;

use App\Http\Requests\Api\ApiFormRequest;

class MarkNotificationsReadRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'ids' => ['sometimes', 'array'],
            'ids.*' => ['integer', 'exists:app_notifications,id'],
        ];
    }
}
