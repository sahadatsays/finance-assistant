<?php

namespace App\Modules\Tenant\Http\Requests\Admin;

use App\Models\Platform\Plan;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('tenants', 'slug')],
            'plan_id' => ['nullable', 'integer', Rule::exists(Plan::class, 'id')->where('is_active', true)],
            'owner_user_id' => ['nullable', 'integer', Rule::exists(User::class, 'id'), 'required_without:owner_email'],
            'owner_email' => ['nullable', 'string', 'email', 'max:255', 'required_without:owner_user_id'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'settings' => ['nullable', 'array'],
            'settings.timezone' => ['nullable', 'string', 'max:64', 'timezone:all'],
            'settings.locale' => ['nullable', 'string', 'max:10'],
            'settings.currency' => ['nullable', 'string', 'size:3'],
        ];
    }
}
