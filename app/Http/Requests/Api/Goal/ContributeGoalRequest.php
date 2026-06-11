<?php

namespace App\Http\Requests\Api\Goal;

use App\Http\Requests\Api\ApiFormRequest;

class ContributeGoalRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:255'],
            'contributed_at' => ['nullable', 'date'],
        ];
    }
}
