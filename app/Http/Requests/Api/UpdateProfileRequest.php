<?php

namespace App\Http\Requests\Api;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'avatar_url' => ['nullable', 'string', 'url', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:64', 'timezone:all'],
            'locale' => ['nullable', 'string', 'max:10'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
