<?php

namespace App\Http\Requests\Api\Goal;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\GoalType;
use Illuminate\Validation\Rule;

class UpdateGoalRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:128'],
            'type' => ['sometimes', 'required', 'string', Rule::enum(GoalType::class)],
            'target_amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'target_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
