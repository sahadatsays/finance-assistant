<?php

namespace App\Http\Requests\Api\Report;

use App\Http\Requests\Api\ApiFormRequest;
use Illuminate\Validation\Rule;

class ExportReportRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report' => ['required', 'string', Rule::in(['summary', 'monthly', 'category', 'cashflow', 'net-worth'])],
            'format' => ['required', 'string', Rule::in(['json', 'csv', 'pdf'])],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'months' => ['sometimes', 'integer', 'min:1', 'max:24'],
        ];
    }
}
