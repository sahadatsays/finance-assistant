<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkspaceSettingsRequest extends FormRequest
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
        $currencyCodes = collect(config('currencies'))->pluck('code')->all();

        return [
            'settings' => ['required', 'array'],
            'settings.currency' => ['required', 'string', Rule::in($currencyCodes)],
        ];
    }
}
