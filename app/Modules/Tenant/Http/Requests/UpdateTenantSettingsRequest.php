<?php

namespace App\Modules\Tenant\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSettingsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'settings' => ['sometimes', 'required', 'array'],
            'settings.timezone' => ['nullable', 'string', 'max:64', 'timezone:all'],
            'settings.locale' => ['nullable', 'string', 'max:10'],
            'settings.currency' => ['nullable', 'string', 'size:3'],
        ];
    }
}
