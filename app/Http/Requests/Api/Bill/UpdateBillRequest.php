<?php

namespace App\Http\Requests\Api\Bill;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\BillRecurrence;
use Illuminate\Validation\Rule;

class UpdateBillRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:128'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'due_date' => ['sometimes', 'required', 'date'],
            'recurrence' => ['sometimes', 'string', Rule::enum(BillRecurrence::class)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
