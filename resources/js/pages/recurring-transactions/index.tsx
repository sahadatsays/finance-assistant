import { Form, Head, router } from '@inertiajs/react';
import RecurringTransactionController from '@/actions/App/Http/Controllers/Finance/RecurringTransactionController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatCurrency } from '@/lib/currency';
import { showValidationErrorToast } from '@/lib/form-errors';
import { cn } from '@/lib/utils';
import {
    ArrowDownLeft,
    ArrowUpRight,
    CalendarClock,
    Pause,
    Pencil,
    Play,
    Plus,
    Repeat,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type Frequency = { value: string; label: string };

type AccountOption = {
    id: number;
    name: string;
    type: string;
    currency: string;
};

type CategoryOption = {
    id: number;
    name: string;
    type: string;
    color: string;
};

type RecurringItem = {
    id: number;
    name: string;
    type: string;
    amount: number;
    account: { id: number; name: string };
    category: { id: number; name: string; color: string };
    frequency: string;
    frequency_label: string;
    run_time: string;
    start_date: string;
    next_run_at: string;
    last_run_at: string | null;
    notes: string | null;
    is_active: boolean;
    transactions_count: number;
};

type Permissions = {
    view: boolean;
    create: boolean;
    update: boolean;
    delete: boolean;
};

type Props = {
    tenant: { id: number; name: string };
    recurringTransactions: RecurringItem[];
    accounts: AccountOption[];
    categories: CategoryOption[];
    frequencies: Frequency[];
    permissions: Permissions;
};

function formatDateTime(value: string | null): string {
    if (!value) {
        return 'Never';
    }

    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
}

function ScheduleFormFields({
    item,
    accounts,
    categories,
    frequencies,
    type,
    onTypeChange,
}: {
    item?: RecurringItem;
    accounts: AccountOption[];
    categories: CategoryOption[];
    frequencies: Frequency[];
    type: 'income' | 'expense';
    onTypeChange: (type: 'income' | 'expense') => void;
}) {
    const filteredCategories = categories.filter((c) => c.type === type);
    const today = new Date().toISOString().slice(0, 10);

    return (
        <>
            <div className="space-y-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={item?.name}
                    placeholder="Salary, Rent, Daily coffee"
                    required
                />
            </div>

            <div className="space-y-2">
                <Label>Type</Label>
                <div className="flex gap-2">
                    {(['income', 'expense'] as const).map((option) => (
                        <Button
                            key={option}
                            type="button"
                            variant={type === option ? 'brand' : 'outline'}
                            size="sm"
                            onClick={() => onTypeChange(option)}
                        >
                            {option === 'income' ? 'Income' : 'Expense'}
                        </Button>
                    ))}
                </div>
                <input type="hidden" name="type" value={type} />
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="amount">Amount</Label>
                    <Input
                        id="amount"
                        name="amount"
                        type="number"
                        min="0.01"
                        step="0.01"
                        defaultValue={item?.amount}
                        required
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="frequency">Frequency</Label>
                    <select
                        id="frequency"
                        name="frequency"
                        defaultValue={item?.frequency ?? 'daily'}
                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                        required
                    >
                        {frequencies.map((frequency) => (
                            <option
                                key={frequency.value}
                                value={frequency.value}
                            >
                                {frequency.label}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="account_id">Account</Label>
                <select
                    id="account_id"
                    name="account_id"
                    defaultValue={item?.account.id}
                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                    required
                >
                    <option value="">Select account</option>
                    {accounts.map((account) => (
                        <option key={account.id} value={account.id}>
                            {account.name} ({account.currency})
                        </option>
                    ))}
                </select>
            </div>

            <div className="space-y-2">
                <Label htmlFor="category_id">Category</Label>
                <select
                    id="category_id"
                    name="category_id"
                    defaultValue={item?.category.id}
                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                    required
                >
                    <option value="">Select category</option>
                    {filteredCategories.map((category) => (
                        <option key={category.id} value={category.id}>
                            {category.name}
                        </option>
                    ))}
                </select>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="start_date">Start date</Label>
                    <DatePicker
                        name="start_date"
                        defaultValue={item?.start_date ?? today}
                        required
                    />
                    <p className="text-xs text-muted-foreground">
                        Bi-weekly schedules repeat every 14 days from this date.
                    </p>
                </div>
                <div className="space-y-2">
                    <Label htmlFor="run_time">Time</Label>
                    <Input
                        id="run_time"
                        name="run_time"
                        type="time"
                        defaultValue={item?.run_time ?? '09:00'}
                        required
                    />
                </div>
            </div>

            <div className="space-y-2">
                <Label htmlFor="notes">Notes (optional)</Label>
                <Input
                    id="notes"
                    name="notes"
                    defaultValue={item?.notes ?? ''}
                    placeholder="Optional description"
                />
            </div>
        </>
    );
}

export default function RecurringTransactionsIndex({
    tenant,
    recurringTransactions,
    accounts,
    categories,
    frequencies,
    permissions,
}: Props) {
    const [createOpen, setCreateOpen] = useState(false);
    const [createFormKey, setCreateFormKey] = useState(0);
    const [createType, setCreateType] = useState<'income' | 'expense'>(
        'expense',
    );
    const [editing, setEditing] = useState<RecurringItem | null>(null);
    const [editType, setEditType] = useState<'income' | 'expense'>('expense');

    const accountCurrencies = useMemo(
        () => new Map(accounts.map((account) => [account.id, account.currency])),
        [accounts],
    );

    const activeCount = recurringTransactions.filter((item) => item.is_active)
        .length;

    const pauseSchedule = (item: RecurringItem) => {
        router.delete(RecurringTransactionController.destroy.url(item.id));
    };

    const resumeSchedule = (item: RecurringItem) => {
        router.post(RecurringTransactionController.resume.url(item.id));
    };

    return (
        <>
            <Head title="Scheduled Transactions" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Scheduled Transactions
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Automate daily expenses and bi-weekly salary for{' '}
                            {tenant.name}
                        </p>
                    </div>
                    {permissions.create && (
                        <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                            <DialogTrigger asChild>
                                <Button variant="brand">
                                    <Plus className="size-4" />
                                    Add schedule
                                </Button>
                            </DialogTrigger>
                            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                                <DialogHeader>
                                    <DialogTitle>
                                        Create scheduled transaction
                                    </DialogTitle>
                                </DialogHeader>
                                <Form
                                    key={createFormKey}
                                    {...RecurringTransactionController.store.form()}
                                    resetOnSuccess
                                    onSuccess={() => {
                                        setCreateOpen(false);
                                        setCreateFormKey((key) => key + 1);
                                        setCreateType('expense');
                                    }}
                                    onError={showValidationErrorToast}
                                    className="space-y-4"
                                >
                                    {({ processing }) => (
                                        <>
                                            <ScheduleFormFields
                                                accounts={accounts}
                                                categories={categories}
                                                frequencies={frequencies}
                                                type={createType}
                                                onTypeChange={setCreateType}
                                            />
                                            <Button
                                                type="submit"
                                                variant="brand"
                                                disabled={processing}
                                            >
                                                Save schedule
                                            </Button>
                                        </>
                                    )}
                                </Form>
                            </DialogContent>
                        </Dialog>
                    )}
                </div>

                <Card className="border-0 bg-muted/40 shadow-sm">
                    <CardContent className="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-sm font-medium">
                                Automatic entries
                            </p>
                            <p className="text-sm text-muted-foreground">
                                The scheduler runs every minute and posts
                                income or expense transactions when they are
                                due. Account balances update like manual
                                entries.
                            </p>
                        </div>
                        <div className="flex shrink-0 gap-4 text-sm">
                            <span className="flex items-center gap-1 text-emerald-600">
                                <ArrowDownLeft className="size-4" />
                                Income
                            </span>
                            <span className="flex items-center gap-1 text-red-600">
                                <ArrowUpRight className="size-4" />
                                Expense
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Active schedules</CardDescription>
                            <CardTitle className="text-2xl">
                                {activeCount}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardDescription>Auto entries posted</CardDescription>
                            <CardTitle className="text-2xl">
                                {recurringTransactions.reduce(
                                    (sum, item) =>
                                        sum + item.transactions_count,
                                    0,
                                )}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                {recurringTransactions.length === 0 ? (
                    <Card className="border-dashed shadow-none">
                        <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                            <CalendarClock className="size-10 text-muted-foreground" />
                            <div>
                                <p className="font-medium">
                                    No scheduled transactions yet
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Set up a daily expense or bi-weekly salary
                                    to post automatically.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {recurringTransactions.map((item) => {
                            const currency =
                                accountCurrencies.get(item.account.id) ??
                                'USD';

                            return (
                                <Card
                                    key={item.id}
                                    className={cn(
                                        'border-0 shadow-sm',
                                        !item.is_active && 'opacity-70',
                                    )}
                                >
                                    <CardHeader className="pb-2">
                                        <div className="flex items-start justify-between gap-2">
                                            <div className="flex items-center gap-3">
                                                <div
                                                    className={cn(
                                                        'flex size-10 items-center justify-center rounded-xl',
                                                        item.type === 'income'
                                                            ? 'bg-emerald-100 text-emerald-700'
                                                            : 'bg-red-100 text-red-700',
                                                    )}
                                                >
                                                    {item.type === 'income' ? (
                                                        <ArrowDownLeft className="size-4" />
                                                    ) : (
                                                        <ArrowUpRight className="size-4" />
                                                    )}
                                                </div>
                                                <div>
                                                    <CardTitle className="text-base">
                                                        {item.name}
                                                    </CardTitle>
                                                    <CardDescription>
                                                        {item.account.name} ·{' '}
                                                        {item.category.name}
                                                    </CardDescription>
                                                </div>
                                            </div>
                                            <div className="flex flex-col items-end gap-1">
                                                <Badge
                                                    variant={
                                                        item.is_active
                                                            ? 'secondary'
                                                            : 'outline'
                                                    }
                                                >
                                                    {item.is_active
                                                        ? 'Active'
                                                        : 'Paused'}
                                                </Badge>
                                                <Badge variant="outline">
                                                    {item.frequency_label}
                                                </Badge>
                                            </div>
                                        </div>
                                    </CardHeader>
                                    <CardContent className="space-y-4">
                                        <p className="text-2xl font-semibold tabular-nums">
                                            {formatCurrency(
                                                item.amount,
                                                currency,
                                            )}
                                        </p>
                                        <div className="grid gap-2 text-sm text-muted-foreground">
                                            <div className="flex items-center gap-2">
                                                <Repeat className="size-4" />
                                                <span>
                                                    At {item.run_time} · starts{' '}
                                                    {item.start_date}
                                                </span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <CalendarClock className="size-4" />
                                                <span>
                                                    Next:{' '}
                                                    {formatDateTime(
                                                        item.next_run_at,
                                                    )}
                                                </span>
                                            </div>
                                            <p>
                                                Last posted:{' '}
                                                {formatDateTime(
                                                    item.last_run_at,
                                                )}{' '}
                                                · {item.transactions_count}{' '}
                                                {item.transactions_count === 1
                                                    ? 'entry'
                                                    : 'entries'}
                                            </p>
                                        </div>
                                        {permissions.update && (
                                            <div className="flex flex-wrap gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => {
                                                        setEditing(item);
                                                        setEditType(
                                                            item.type as
                                                                | 'income'
                                                                | 'expense',
                                                        );
                                                    }}
                                                >
                                                    <Pencil className="size-4" />
                                                    Edit
                                                </Button>
                                                {item.is_active ? (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            pauseSchedule(item)
                                                        }
                                                    >
                                                        <Pause className="size-4" />
                                                        Pause
                                                    </Button>
                                                ) : (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            resumeSchedule(item)
                                                        }
                                                    >
                                                        <Play className="size-4" />
                                                        Resume
                                                    </Button>
                                                )}
                                            </div>
                                        )}
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>
                )}
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setEditing(null);
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Edit scheduled transaction</DialogTitle>
                    </DialogHeader>
                    {editing && (
                        <Form
                            {...RecurringTransactionController.update.form(
                                editing.id,
                            )}
                            onSuccess={() => setEditing(null)}
                            onError={showValidationErrorToast}
                            className="space-y-4"
                        >
                            {({ processing }) => (
                                <>
                                    <ScheduleFormFields
                                        item={editing}
                                        accounts={accounts}
                                        categories={categories}
                                        frequencies={frequencies}
                                        type={editType}
                                        onTypeChange={setEditType}
                                    />
                                    <Button
                                        type="submit"
                                        variant="brand"
                                        disabled={processing}
                                    >
                                        Save changes
                                    </Button>
                                </>
                            )}
                        </Form>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}
