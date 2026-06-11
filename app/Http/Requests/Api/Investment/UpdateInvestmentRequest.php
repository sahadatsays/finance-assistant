<?php

namespace App\Http\Requests\Api\Investment;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\InvestmentType;
use Illuminate\Validation\Rule;

class UpdateInvestmentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:128'],
            'symbol' => ['nullable', 'string', 'max:32'],
            'type' => ['sometimes', 'string', Rule::enum(InvestmentType::class)],
            'quantity' => ['sometimes', 'numeric', 'min:0.00000001'],
            'cost_basis' => ['sometimes', 'numeric', 'min:0'],
            'current_price' => ['sometimes', 'numeric', 'min:0'],
            'purchased_at' => ['nullable', 'date'],
        ];
    }
}
