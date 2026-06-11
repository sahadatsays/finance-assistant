<?php

namespace App\Http\Requests\Api\Account;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\AccountType;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128'],
            'type' => ['required', 'string', Rule::enum(AccountType::class)],
            'balance' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}
