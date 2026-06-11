<?php

namespace App\Http\Requests\Api\Budget;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\BudgetPeriodType;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'period_type' => ['required', 'string', Rule::enum(BudgetPeriodType::class)],
            'period_start' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.category_id' => ['required', 'integer', 'exists:categories,id'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
