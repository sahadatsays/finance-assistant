import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useState } from 'react';

export type TransactionFormAccount = {
    id: number;
    name: string;
    type: string;
};

export type TransactionFormCategory = {
    id: number;
    name: string;
    type: string;
    color: string;
};

export type TransactionFormData = {
    type: string;
    amount: number;
    notes: string | null;
    occurred_at: string;
    account: { id: number; name: string } | null;
    transfer_account: { id: number; name: string } | null;
    category: { id: number; name: string; color: string } | null;
    tags: { id: number; name: string }[];
};

type Props = {
    accounts: TransactionFormAccount[];
    categories: TransactionFormCategory[];
    defaultType?: string;
    transaction?: TransactionFormData;
    compact?: boolean;
};

export default function TransactionFormFields({
    accounts,
    categories,
    defaultType = 'expense',
    transaction,
    compact = false,
}: Props) {
    const [type, setType] = useState(transaction?.type ?? defaultType);

    const filteredCategories = categories.filter((c) => c.type === type);

    return (
        <>
            <div className="grid gap-2">
                <Label>Type</Label>
                <select
                    name="type"
                    value={type}
                    onChange={(e) => setType(e.target.value)}
                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                >
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
            <div className="grid gap-2">
                <Label>Account</Label>
                <select
                    name="account_id"
                    defaultValue={transaction?.account?.id}
                    required
                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                >
                    <option value="">Select account</option>
                    {accounts.map((a) => (
                        <option key={a.id} value={a.id}>
                            {a.name}
                        </option>
                    ))}
                </select>
            </div>
            {type === 'transfer' && (
                <div className="grid gap-2">
                    <Label>Transfer To</Label>
                    <select
                        name="transfer_account_id"
                        defaultValue={transaction?.transfer_account?.id}
                        required
                        className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                    >
                        <option value="">Select account</option>
                        {accounts.map((a) => (
                            <option key={a.id} value={a.id}>
                                {a.name}
                            </option>
                        ))}
                    </select>
                </div>
            )}
            {type !== 'transfer' && (
                <div className="grid gap-2">
                    <Label>Category</Label>
                    <select
                        name="category_id"
                        defaultValue={transaction?.category?.id}
                        required
                        className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                    >
                        <option value="">Select category</option>
                        {filteredCategories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                </div>
            )}
            <div className="grid gap-2">
                <Label>Amount</Label>
                <Input
                    name="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    defaultValue={transaction?.amount}
                    required
                />
            </div>
            <div className="grid gap-2">
                <Label>Date</Label>
                <DatePicker
                    name="occurred_at"
                    defaultValue={
                        transaction
                            ? transaction.occurred_at.slice(0, 10)
                            : new Date().toISOString().slice(0, 10)
                    }
                    required
                />
            </div>
            {!compact && (
                <>
                    <div className="grid gap-2 md:col-span-2">
                        <Label>Notes</Label>
                        <Input
                            name="notes"
                            defaultValue={transaction?.notes ?? ''}
                            placeholder="Optional notes"
                        />
                    </div>
                    <div className="grid gap-2 md:col-span-2">
                        <Label>Tags</Label>
                        <Input
                            name="tags"
                            defaultValue={transaction?.tags
                                .map((t) => t.name)
                                .join(', ')}
                            placeholder="personal, recurring (comma-separated)"
                        />
                    </div>
                    <div className="grid gap-2 md:col-span-2">
                        <Label>Attachment</Label>
                        <Input
                            name="attachment"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                        />
                    </div>
                </>
            )}
        </>
    );
}
