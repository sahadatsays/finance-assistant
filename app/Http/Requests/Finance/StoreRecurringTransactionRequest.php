<?php

namespace App\Http\Requests\Finance;

use App\Modules\Finance\Enums\RecurrenceFrequency;
use App\Modules\Finance\Enums\TransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:128'],
            'type' => ['required', 'string', Rule::in([
                TransactionType::Income->value,
                TransactionType::Expense->value,
            ])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'frequency' => ['required', 'string', Rule::enum(RecurrenceFrequency::class)],
            'run_time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
