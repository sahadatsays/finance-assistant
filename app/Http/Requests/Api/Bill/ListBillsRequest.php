<?php

namespace App\Http\Requests\Api\Bill;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\BillStatus;
use Illuminate\Validation\Rule;

class ListBillsRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::enum(BillStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return array_filter([
            'status' => $this->validated('status'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
