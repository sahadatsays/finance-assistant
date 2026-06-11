<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:128'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'lines' => ['sometimes', 'required', 'array', 'min:1'],
            'lines.*.category_id' => ['required', 'integer', 'exists:categories,id'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
