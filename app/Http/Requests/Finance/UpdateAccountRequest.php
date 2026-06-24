<?php

namespace App\Http\Requests\Finance;

use App\Modules\Finance\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
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
            'type' => ['required', 'string', Rule::enum(AccountType::class)],
            'currency' => ['sometimes', 'string', 'size:3'],
        ];
    }
}
