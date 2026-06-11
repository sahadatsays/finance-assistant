<?php

namespace App\Http\Requests\Api\Goal;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\GoalType;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'type' => ['required', 'string', Rule::enum(GoalType::class)],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'initial_contribution' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }
}
