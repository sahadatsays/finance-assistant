<?php

namespace App\Http\Requests\Api\Investment;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\InvestmentType;
use Illuminate\Validation\Rule;

class StoreInvestmentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'symbol' => ['nullable', 'string', 'max:32'],
            'type' => ['required', 'string', Rule::enum(InvestmentType::class)],
            'quantity' => ['required', 'numeric', 'min:0.00000001'],
            'cost_basis' => ['required', 'numeric', 'min:0'],
            'current_price' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['nullable', 'date'],
        ];
    }
}
