import { Form, Head, Link, router } from '@inertiajs/react';
import TransactionController from '@/actions/App/Http/Controllers/Finance/TransactionController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import TransactionFormFields from '@/components/transactions/transaction-form-fields';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';
import { exportMethod as exportTransactions } from '@/routes/transactions';
import {
    ArrowDownLeft,
    ArrowLeftRight,
    ArrowUpRight,
    Download,
    Pencil,
    Plus,
    Search,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';

type TransactionItem = {
    id: number;
    type: string;
    amount: number;
    notes: string | null;
    occurred_at: string;
    account: { id: number; name: string } | null;
    transfer_account: { id: number; name: string } | null;
    category: { id: number; name: string; color: string } | null;
    tags: { id: number; name: string }[];
    attachments: { id: number; original_name: string; size: number }[];
};

type Props = {
    tenant: { id: number; name: string };
    transactions: TransactionItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        type?: string;
        category_id?: string;
        account_id?: string;
        tag_id?: string;
        date_from?: string;
        date_to?: string;
    };
    accounts: { id: number; name: string; type: string }[];
    categories: { id: number; name: string; type: string; color: string }[];
    tags: { id: number; name: string }[];
    permissions: {
        view: boolean;
        create: boolean;
        update: boolean;
        delete: boolean;
        export: boolean;
    };
};

const typeColors: Record<string, string> = {
    income: 'bg-emerald-100 text-emerald-700',
    expense: 'bg-rose-100 text-rose-700',
    transfer: 'bg-blue-100 text-blue-700',
};

function TypeIcon({ type }: { type: string }) {
    if (type === 'income') {
        return <ArrowDownLeft className="size-4" />;
    }
    if (type === 'transfer') {
        return <ArrowLeftRight className="size-4" />;
    }
    return <ArrowUpRight className="size-4" />;
}

export default function TransactionsIndex({
    tenant,
    transactions,
    meta,
    filters,
    accounts,
    categories,
    tags,
    permissions,
}: Props) {
    const { formatCurrency } = useCurrency();
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<TransactionItem | null>(null);

    const applyFilters = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const form = new FormData(e.currentTarget);
        router.get(
            TransactionController.index.url(),
            Object.fromEntries(form.entries()),
            { preserveState: true },
        );
    };

    const exportUrl = exportTransactions.url({
        query: filters as Record<string, string>,
    });

    return (
        <>
            <Head title="Transactions" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Transactions</h1>
                        <p className="text-sm text-muted-foreground">
                            Manage income, expenses, and transfers for {tenant.name}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {permissions.export && (
                            <Button variant="outline" asChild>
                                <a href={exportUrl}>
                                    <Download className="mr-2 size-4" />
                                    Export CSV
                                </a>
                            </Button>
                        )}
                        {permissions.create && (
                            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                                <DialogTrigger asChild>
                                    <Button variant="brand">
                                        <Plus className="mr-2 size-4" />
                                        Add Transaction
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-w-lg">
                                    <DialogHeader>
                                        <DialogTitle>New Transaction</DialogTitle>
                                    </DialogHeader>
                                    <Form
                                        {...TransactionController.store.form()}
                                        encType="multipart/form-data"
                                        className="grid gap-4 md:grid-cols-2"
                                        onSuccess={() => setCreateOpen(false)}
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <TransactionFormFields
                                                    accounts={accounts}
                                                    categories={categories}
                                                />
                                                {errors.transaction && (
                                                    <p className="text-sm text-destructive md:col-span-2">
                                                        {errors.transaction}
                                                    </p>
                                                )}
                                                <div className="md:col-span-2">
                                                    <Button
                                                        type="submit"
                                                        variant="brand"
                                                        disabled={processing}
                                                    >
                                                        Create Transaction
                                                    </Button>
                                                </div>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Search & Filter</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={applyFilters}
                            className="grid gap-4 md:grid-cols-4 lg:grid-cols-6"
                        >
                            <div className="grid gap-2 md:col-span-2">
                                <Label>Search</Label>
                                <div className="relative">
                                    <Search className="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                                    <Input
                                        name="search"
                                        defaultValue={filters.search}
                                        placeholder="Notes, tags, amount..."
                                        className="pl-8"
                                    />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label>Type</Label>
                                <select
                                    name="type"
                                    defaultValue={filters.type ?? ''}
                                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="">All</option>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Category</Label>
                                <select
                                    name="category_id"
                                    defaultValue={filters.category_id ?? ''}
                                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="">All</option>
                                    {categories.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Account</Label>
                                <select
                                    name="account_id"
                                    defaultValue={filters.account_id ?? ''}
                                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="">All</option>
                                    {accounts.map((a) => (
                                        <option key={a.id} value={a.id}>
                                            {a.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Tag</Label>
                                <select
                                    name="tag_id"
                                    defaultValue={filters.tag_id ?? ''}
                                    className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                >
                                    <option value="">All</option>
                                    {tags.map((t) => (
                                        <option key={t.id} value={t.id}>
                                            {t.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>From</Label>
                                <DatePicker
                                    name="date_from"
                                    defaultValue={filters.date_from}
                                    placeholder="Start date"
                                />
                            </div>
                            <div className="grid gap-2">
                                <Label>To</Label>
                                <DatePicker
                                    name="date_to"
                                    defaultValue={filters.date_to}
                                    placeholder="End date"
                                />
                            </div>
                            <div className="flex items-end">
                                <Button type="submit" variant="secondary">
                                    Apply Filters
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card className="border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle>Transactions</CardTitle>
                        <CardDescription>
                            {meta.total} total records
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-3">
                        {transactions.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No transactions found
                            </p>
                        ) : (
                            transactions.map((tx) => (
                                <div
                                    key={tx.id}
                                    className="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="flex items-start gap-3">
                                        <div
                                            className={cn(
                                                'flex size-10 items-center justify-center rounded-xl',
                                                tx.type === 'income'
                                                    ? 'bg-emerald-500/10 text-emerald-600'
                                                    : tx.type === 'transfer'
                                                      ? 'bg-blue-500/10 text-blue-600'
                                                      : 'bg-rose-500/10 text-rose-600',
                                            )}
                                        >
                                            <TypeIcon type={tx.type} />
                                        </div>
                                        <div>
                                            <div className="flex flex-wrap items-center gap-2">
                                                <p className="font-medium">
                                                    {tx.notes ||
                                                        tx.category?.name ||
                                                        (tx.type === 'transfer'
                                                            ? 'Transfer'
                                                            : 'Transaction')}
                                                </p>
                                                <Badge
                                                    className={cn(
                                                        typeColors[tx.type],
                                                    )}
                                                >
                                                    {tx.type}
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                {tx.account?.name}
                                                {tx.transfer_account &&
                                                    ` → ${tx.transfer_account.name}`}
                                                {tx.category &&
                                                    ` · ${tx.category.name}`}
                                                {' · '}
                                                {new Date(
                                                    tx.occurred_at,
                                                ).toLocaleDateString()}
                                            </p>
                                            {tx.tags.length > 0 && (
                                                <div className="mt-1 flex flex-wrap gap-1">
                                                    {tx.tags.map((tag) => (
                                                        <Badge
                                                            key={tag.id}
                                                            variant="outline"
                                                            className="text-xs"
                                                        >
                                                            {tag.name}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            )}
                                            {tx.attachments.length > 0 && (
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    📎{' '}
                                                    {tx.attachments
                                                        .map((a) => a.original_name)
                                                        .join(', ')}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <span
                                            className={cn(
                                                'text-lg font-semibold',
                                                tx.type === 'income'
                                                    ? 'text-emerald-600'
                                                    : tx.type === 'expense'
                                                      ? 'text-rose-600'
                                                      : 'text-blue-600',
                                            )}
                                        >
                                            {tx.type === 'income' ? '+' : tx.type === 'expense' ? '-' : ''}
                                            {formatCurrency(tx.amount)}
                                        </span>
                                        {permissions.update && (
                                            <Dialog
                                                open={editing?.id === tx.id}
                                                onOpenChange={(open) =>
                                                    !open && setEditing(null)
                                                }
                                            >
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            setEditing(tx)
                                                        }
                                                    >
                                                        <Pencil className="size-3" />
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent className="max-w-lg">
                                                    <DialogHeader>
                                                        <DialogTitle>
                                                            Edit Transaction
                                                        </DialogTitle>
                                                    </DialogHeader>
                                                    <Form
                                                        {...TransactionController.update.form(
                                                            tx.id,
                                                        )}
                                                        encType="multipart/form-data"
                                                        className="grid gap-4 md:grid-cols-2"
                                                        onSuccess={() =>
                                                            setEditing(null)
                                                        }
                                                    >
                                                        {({
                                                            processing,
                                                            errors,
                                                        }) => (
                                                            <>
                                                                <TransactionFormFields
                                                                    accounts={
                                                                        accounts
                                                                    }
                                                                    categories={
                                                                        categories
                                                                    }
                                                                    transaction={
                                                                        tx
                                                                    }
                                                                />
                                                                {errors.transaction && (
                                                                    <p className="text-sm text-destructive md:col-span-2">
                                                                        {
                                                                            errors.transaction
                                                                        }
                                                                    </p>
                                                                )}
                                                                <div className="md:col-span-2">
                                                                    <Button
                                                                        type="submit"
                                                                        variant="brand"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                    >
                                                                        Save Changes
                                                                    </Button>
                                                                </div>
                                                            </>
                                                        )}
                                                    </Form>
                                                </DialogContent>
                                            </Dialog>
                                        )}
                                        {permissions.delete && (
                                            <Link
                                                href={TransactionController.destroy.url(
                                                    tx.id,
                                                )}
                                                method="delete"
                                                as="button"
                                                className="inline-flex size-8 items-center justify-center rounded-md border border-rose-200 text-rose-600 hover:bg-rose-50"
                                            >
                                                <Trash2 className="size-3" />
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            ))
                        )}

                        {meta.last_page > 1 && (
                            <div className="flex items-center justify-between pt-4">
                                <p className="text-sm text-muted-foreground">
                                    Page {meta.current_page} of {meta.last_page}
                                </p>
                                <div className="flex gap-2">
                                    {meta.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.get(
                                                    TransactionController.index.url(),
                                                    {
                                                        ...filters,
                                                        page: meta.current_page - 1,
                                                    },
                                                )
                                            }
                                        >
                                            Previous
                                        </Button>
                                    )}
                                    {meta.current_page < meta.last_page && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                router.get(
                                                    TransactionController.index.url(),
                                                    {
                                                        ...filters,
                                                        page: meta.current_page + 1,
                                                    },
                                                )
                                            }
                                        >
                                            Next
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
