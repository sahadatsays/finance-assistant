<?php

namespace App\Http\Requests\Api\Auth;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Requests\Api\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
