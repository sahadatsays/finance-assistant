<?php

namespace App\Modules\Tenant\Http\Requests;

use App\Modules\Tenant\Enums\TenantUserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantUserRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::enum(TenantUserRole::class)],
        ];
    }
}
