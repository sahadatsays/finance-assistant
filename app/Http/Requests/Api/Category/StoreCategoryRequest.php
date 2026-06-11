<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\CategoryType;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', Rule::enum(CategoryType::class)],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', Rule::in(config('category-icons'))],
        ];
    }
}
