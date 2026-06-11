<?php

namespace App\Http\Requests\Api\Transaction;

use App\Http\Requests\Api\ApiFormRequest;
use App\Modules\Finance\Enums\TransactionType;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->input('type');

        return [
            'type' => ['sometimes', 'required', 'string', Rule::enum(TransactionType::class)],
            'account_id' => ['sometimes', 'required', 'integer', 'exists:accounts,id'],
            'transfer_account_id' => [
                Rule::requiredIf(fn () => $type === TransactionType::Transfer->value),
                'nullable',
                'integer',
                'exists:accounts,id',
            ],
            'category_id' => [
                Rule::requiredIf(fn () => $type !== null && $type !== TransactionType::Transfer->value),
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'occurred_at' => ['sometimes', 'required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,webp'],
        ];
    }
}
