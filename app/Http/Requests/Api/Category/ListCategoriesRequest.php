<?php

namespace App\Http\Requests\Api\Category;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\CategoryKind;
use App\Modules\Finance\Enums\CategoryType;
use Illuminate\Validation\Rule;

class ListCategoriesRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::enum(CategoryType::class)],
            'kind' => ['sometimes', 'string', Rule::enum(CategoryKind::class)],
            'archived' => ['sometimes', 'boolean'],
            'search' => ['sometimes', 'string', 'max:64'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array{type?: string, kind?: string, archived?: bool, search?: string}
     */
    public function filters(): array
    {
        return array_filter([
            'type' => $this->validated('type'),
            'kind' => $this->validated('kind'),
            'archived' => $this->has('archived') ? $this->boolean('archived') : null,
            'search' => $this->validated('search'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
