<?php

namespace App\Http\Requests\Api\Budget;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\BudgetPeriodType;
use Illuminate\Validation\Rule;

class ListBudgetsRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'period_type' => ['sometimes', 'string', Rule::enum(BudgetPeriodType::class)],
            'sort' => ['sometimes', 'string', Rule::in(['period_start', 'period_end', 'amount', 'name', 'created_at'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
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
            'period_type' => $this->validated('period_type'),
            'sort' => $this->validated('sort'),
            'direction' => $this->validated('direction'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
