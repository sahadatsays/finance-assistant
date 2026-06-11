<?php

namespace App\Http\Requests\Api\Transaction;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\TransactionType;
use Illuminate\Validation\Rule;

class ListTransactionsRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', Rule::enum(TransactionType::class)],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'account_id' => ['sometimes', 'integer', 'exists:accounts,id'],
            'tag_id' => ['sometimes', 'integer', 'exists:tags,id'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'amount_min' => ['sometimes', 'numeric', 'min:0'],
            'amount_max' => ['sometimes', 'numeric', 'min:0'],
            'sort' => ['sometimes', 'string', Rule::in(['occurred_at', 'amount', 'created_at', 'type'])],
            'direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        return array_filter([
            'search' => $this->validated('search'),
            'type' => $this->validated('type'),
            'category_id' => $this->validated('category_id'),
            'account_id' => $this->validated('account_id'),
            'tag_id' => $this->validated('tag_id'),
            'date_from' => $this->validated('date_from'),
            'date_to' => $this->validated('date_to'),
            'amount_min' => $this->validated('amount_min'),
            'amount_max' => $this->validated('amount_max'),
            'sort' => $this->validated('sort'),
            'direction' => $this->validated('direction'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
