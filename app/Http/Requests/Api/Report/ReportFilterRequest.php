<?php

namespace App\Http\Requests\Api\Report;

use App\Http\Requests\Api\ApiFormRequest;

class ReportFilterRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'months' => ['sometimes', 'integer', 'min:1', 'max:24'],
        ];
    }
}
