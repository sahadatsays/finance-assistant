<?php

namespace App\Modules\Finance\Services;

use App\Models\Finance\Account;
use App\Models\Finance\Attachment;
use App\Models\Finance\Category;
use App\Models\Finance\Transaction;
use App\Models\Platform\Tenant;
use App\Models\User;
use App\Modules\Finance\Enums\CategoryType;
use App\Modules\Finance\Enums\TransactionType;
use App\Services\Platform\ActivityLogService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        private ActivityLogService $activityLog,
        private TagService $tags,
    ) {}

    /**
     * @param  array{
     *     search?: string,
     *     type?: string,
     *     category_id?: int,
     *     account_id?: int,
     *     tag_id?: int,
     *     date_from?: string,
     *     date_to?: string
     * }  $filters
     * @return LengthAwarePaginator<int, Transaction>
     */
    public function paginate(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Transaction::query()
            ->with(['category', 'account', 'transferAccount', 'tags', 'attachments'])
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search): void {
                $q->where('notes', 'like', $search)
                    ->orWhere('amount', 'like', $search)
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $search))
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $search));
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['account_id'])) {
            $query->where(function ($q) use ($filters): void {
                $q->where('account_id', $filters['account_id'])
                    ->orWhere('transfer_account_id', $filters['account_id']);
            });
        }

        if (! empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $filters['tag_id']));
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('occurred_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('occurred_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  array{
     *     type: string,
     *     account_id: int,
     *     transfer_account_id?: int|null,
     *     category_id?: int|null,
     *     amount: float|string,
     *     occurred_at: string,
     *     notes?: string|null,
     *     tags?: list<string>
     * }  $data
     */
    public function create(Tenant $tenant, array $data, User $user, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($tenant, $data, $user, $attachment): Transaction {
            $type = TransactionType::from($data['type']);
            $this->validateTransactionData($tenant, $type, $data);

            $transaction = Transaction::query()->create([
                'tenant_id' => $tenant->id,
                'account_id' => $data['account_id'],
                'transfer_account_id' => $data['transfer_account_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'type' => $type,
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
                'occurred_at' => $data['occurred_at'],
                'created_by' => $user->id,
            ]);

            $this->applyBalanceEffect($transaction);
            $this->tags->syncForTransaction($tenant, $transaction, $data['tags'] ?? []);

            if ($attachment !== null) {
                $this->storeAttachment($tenant, $transaction, $attachment, $user);
            }

            $this->activityLog->log(
                "Transaction ({$type->value}) of {$transaction->amount} was created.",
                logName: 'finance',
                subject: $transaction,
                causer: $user,
                tenant: $tenant,
            );

            return $transaction->load(['category', 'account', 'transferAccount', 'tags', 'attachments']);
        });
    }

    /**
     * @param  array{
     *     type?: string,
     *     account_id?: int,
     *     transfer_account_id?: int|null,
     *     category_id?: int|null,
     *     amount?: float|string,
     *     occurred_at?: string,
     *     notes?: string|null,
     *     tags?: list<string>
     * }  $data
     */
    public function update(Transaction $transaction, array $data, User $user, ?UploadedFile $attachment = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $data, $user, $attachment): Transaction {
            $this->reverseBalanceEffect($transaction);

            $type = isset($data['type'])
                ? TransactionType::from($data['type'])
                : $transaction->type;

            $merged = [
                'account_id' => $data['account_id'] ?? $transaction->account_id,
                'transfer_account_id' => $data['transfer_account_id'] ?? $transaction->transfer_account_id,
                'category_id' => $data['category_id'] ?? $transaction->category_id,
                'amount' => $data['amount'] ?? $transaction->amount,
                'occurred_at' => $data['occurred_at'] ?? $transaction->occurred_at->toDateTimeString(),
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $transaction->notes,
            ];

            $this->validateTransactionData($transaction->tenant, $type, array_merge($merged, ['type' => $type->value]));

            $transaction->update([
                ...$merged,
                'type' => $type,
                'updated_by' => $user->id,
            ]);

            $transaction = $transaction->fresh();
            $this->applyBalanceEffect($transaction);

            if (array_key_exists('tags', $data)) {
                $this->tags->syncForTransaction($transaction->tenant, $transaction, $data['tags'] ?? []);
            }

            if ($attachment !== null) {
                $this->storeAttachment($transaction->tenant, $transaction, $attachment, $user);
            }

            $this->activityLog->log(
                "Transaction #{$transaction->id} was updated.",
                logName: 'finance',
                subject: $transaction,
                causer: $user,
                tenant: $transaction->tenant,
            );

            return $transaction->load(['category', 'account', 'transferAccount', 'tags', 'attachments']);
        });
    }

    public function delete(Transaction $transaction, User $user): void
    {
        DB::transaction(function () use ($transaction, $user): void {
            $this->reverseBalanceEffect($transaction);

            foreach ($transaction->attachments as $file) {
                Storage::disk('local')->delete($file->path);
                $file->delete();
            }

            $transaction->tags()->detach();
            $id = $transaction->id;
            $tenant = $transaction->tenant;

            $transaction->delete();

            $this->activityLog->log(
                "Transaction #{$id} was deleted.",
                logName: 'finance',
                causer: $user,
                tenant: $tenant,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Transaction>
     */
    public function exportCollection(Tenant $tenant, array $filters = []): Collection
    {
        return $this->paginate($tenant, $filters, perPage: 10000)->getCollection();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateTransactionData(Tenant $tenant, TransactionType $type, array $data): void
    {
        $account = Account::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $data['account_id'])
            ->where('is_active', true)
            ->first();

        if ($account === null) {
            throw new InvalidArgumentException('Invalid account selected.');
        }

        if ((float) $data['amount'] <= 0) {
            throw new InvalidArgumentException('Amount must be greater than zero.');
        }

        if ($type === TransactionType::Transfer) {
            if (empty($data['transfer_account_id'])) {
                throw new InvalidArgumentException('Transfer requires a destination account.');
            }

            if ((int) $data['transfer_account_id'] === (int) $data['account_id']) {
                throw new InvalidArgumentException('Transfer accounts must be different.');
            }

            $destination = Account::query()
                ->where('tenant_id', $tenant->id)
                ->where('id', $data['transfer_account_id'])
                ->where('is_active', true)
                ->exists();

            if (! $destination) {
                throw new InvalidArgumentException('Invalid destination account.');
            }

            return;
        }

        if (empty($data['category_id'])) {
            throw new InvalidArgumentException('Category is required for income and expense transactions.');
        }

        $expectedType = $type === TransactionType::Income ? CategoryType::Income : CategoryType::Expense;

        $categoryValid = Category::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $data['category_id'])
            ->where('type', $expectedType)
            ->where('is_active', true)
            ->exists();

        if (! $categoryValid) {
            throw new InvalidArgumentException('Invalid category for this transaction type.');
        }
    }

    private function applyBalanceEffect(Transaction $transaction): void
    {
        $amount = (float) $transaction->amount;

        match ($transaction->type) {
            TransactionType::Income => Account::query()
                ->where('id', $transaction->account_id)
                ->increment('balance', $amount),
            TransactionType::Expense => Account::query()
                ->where('id', $transaction->account_id)
                ->decrement('balance', $amount),
            TransactionType::Transfer => $this->applyTransfer($transaction, $amount),
        };
    }

    private function reverseBalanceEffect(Transaction $transaction): void
    {
        $amount = (float) $transaction->amount;

        match ($transaction->type) {
            TransactionType::Income => Account::query()
                ->where('id', $transaction->account_id)
                ->decrement('balance', $amount),
            TransactionType::Expense => Account::query()
                ->where('id', $transaction->account_id)
                ->increment('balance', $amount),
            TransactionType::Transfer => $this->reverseTransfer($transaction, $amount),
        };
    }

    private function applyTransfer(Transaction $transaction, float $amount): void
    {
        Account::query()->where('id', $transaction->account_id)->decrement('balance', $amount);
        Account::query()->where('id', $transaction->transfer_account_id)->increment('balance', $amount);
    }

    private function reverseTransfer(Transaction $transaction, float $amount): void
    {
        Account::query()->where('id', $transaction->account_id)->increment('balance', $amount);
        Account::query()->where('id', $transaction->transfer_account_id)->decrement('balance', $amount);
    }

    private function storeAttachment(
        Tenant $tenant,
        Transaction $transaction,
        UploadedFile $file,
        User $user,
    ): Attachment {
        $path = $file->store("attachments/{$tenant->id}/{$transaction->id}", 'local');

        return Attachment::query()->create([
            'tenant_id' => $tenant->id,
            'transaction_id' => $transaction->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);
    }
}
