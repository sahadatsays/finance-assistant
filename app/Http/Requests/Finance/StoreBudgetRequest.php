<?php

namespace App\Http\Requests\Finance;

use App\Modules\Finance\Enums\BudgetPeriodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
