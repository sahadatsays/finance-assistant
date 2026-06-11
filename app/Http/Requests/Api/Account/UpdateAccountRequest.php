<?php

namespace App\Http\Requests\Api\Account;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\AccountType;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:128'],
            'type' => ['sometimes', 'required', 'string', Rule::enum(AccountType::class)],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}
