<?php

namespace App\Http\Requests\Api\Goal;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\GoalType;
use Illuminate\Validation\Rule;

class ListGoalsRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::enum(GoalType::class)],
            'search' => ['sometimes', 'string', 'max:128'],
            'sort' => ['sometimes', 'string', Rule::in(['target_date', 'target_amount', 'current_amount', 'name', 'created_at'])],
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
            'type' => $this->validated('type'),
            'search' => $this->validated('search'),
            'sort' => $this->validated('sort'),
            'direction' => $this->validated('direction'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
