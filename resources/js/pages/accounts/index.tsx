import { Form, Head, router } from '@inertiajs/react';
import AccountController from '@/actions/App/Http/Controllers/Finance/AccountController';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCurrency } from '@/hooks/use-currency';
import { formatCurrency } from '@/lib/currency';
import { showValidationErrorToast } from '@/lib/form-errors';
import { cn } from '@/lib/utils';
import {
    ArrowDownLeft,
    ArrowUpRight,
    Banknote,
    CreditCard,
    Landmark,
    Pencil,
    PiggyBank,
    Plus,
    Trash2,
    Wallet,
} from 'lucide-react';
import { useMemo, useState } from 'react';

type AccountType = { value: string; label: string };

type CurrencyOption = { code: string; symbol: string; label: string };

type AccountItem = {
    id: number;
    name: string;
    type: string;
    type_label: string;
    balance: number;
    currency: string;
    transactions_count: number;
    can_delete: boolean;
};

type Permissions = {
    view: boolean;
    create: boolean;
    update: boolean;
    delete: boolean;
};

type Props = {
    tenant: { id: number; name: string };
    accounts: AccountItem[];
    summary: {
        account_count: number;
        by_currency: {
            currency: string;
            total_balance: number;
            account_count: number;
        }[];
    };
    accountTypes: AccountType[];
    currencies: CurrencyOption[];
    permissions: Permissions;
};

const typeIcons: Record<string, typeof Wallet> = {
    checking: Landmark,
    savings: PiggyBank,
    credit: CreditCard,
    cash: Banknote,
};

function TypeIcon({ type }: { type: string }) {
    const Icon = typeIcons[type] ?? Wallet;

    return <Icon className="size-4" />;
}

function AccountFormFields({
    accountTypes,
    currencies,
    defaultCurrency,
    account,
}: {
    accountTypes: AccountType[];
    currencies: CurrencyOption[];
    defaultCurrency: string;
    account?: AccountItem;
}) {
    return (
        <>
            <div className="space-y-2">
                <Label htmlFor="name">Account name</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={account?.name}
                    placeholder="Main Checking"
                    required
                />
            </div>
            <div className="space-y-2">
                <Label htmlFor="type">Type</Label>
                <select
                    id="type"
                    name="type"
                    defaultValue={account?.type ?? 'checking'}
                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                    required
                >
                    {accountTypes.map((type) => (
                        <option key={type.value} value={type.value}>
                            {type.label}
                        </option>
                    ))}
                </select>
            </div>
            {!account && (
                <div className="space-y-2">
                    <Label htmlFor="balance">Starting balance</Label>
                    <Input
                        id="balance"
                        name="balance"
                        type="number"
                        min="0"
                        step="0.01"
                        defaultValue="0"
                    />
                    <p className="text-xs text-muted-foreground">
                        Set your current balance once. After that, income and
                        expenses update it automatically.
                    </p>
                </div>
            )}
            <div className="space-y-2">
                <Label htmlFor="currency">Currency</Label>
                <select
                    id="currency"
                    name="currency"
                    defaultValue={account?.currency ?? defaultCurrency}
                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                >
                    {currencies.map((currency) => (
                        <option key={currency.code} value={currency.code}>
                            {currency.code} — {currency.label}
                        </option>
                    ))}
                </select>
            </div>
        </>
    );
}

export default function AccountsIndex({
    tenant,
    accounts,
    summary,
    accountTypes,
    currencies,
    permissions,
}: Props) {
    const { currency: workspaceCurrency } = useCurrency();
    const [editing, setEditing] = useState<AccountItem | null>(null);
    const [createOpen, setCreateOpen] = useState(false);
    const [createFormKey, setCreateFormKey] = useState(0);

    const currencyLabels = useMemo(
        () => new Map(currencies.map((currency) => [currency.code, currency])),
        [currencies],
    );

    const deleteAccount = (account: AccountItem) => {
        router.delete(AccountController.destroy.url(account.id));
    };

    return (
        <>
            <Head title="Accounts" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Accounts</h1>
                        <p className="text-sm text-muted-foreground">
                            Track where money lives in {tenant.name}
                        </p>
                    </div>
                    {permissions.create && (
                        <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                            <DialogTrigger asChild>
                                <Button variant="brand">
                                    <Plus className="size-4" />
                                    Add account
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Create account</DialogTitle>
                                </DialogHeader>
                                <Form
                                    key={createFormKey}
                                    {...AccountController.store.form()}
                                    resetOnSuccess
                                    onSuccess={() => {
                                        setCreateOpen(false);
                                        setCreateFormKey((k) => k + 1);
                                    }}
                                    onError={showValidationErrorToast}
                                    className="space-y-4"
                                >
                                    {({ processing }) => (
                                        <>
                                            <AccountFormFields
                                                accountTypes={accountTypes}
                                                currencies={currencies}
                                                defaultCurrency={
                                                    workspaceCurrency
                                                }
                                            />
                                            <Button
                                                type="submit"
                                                variant="brand"
                                                disabled={processing}
                                            >
                                                Create account
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
                                How balances work
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Income transactions add money to an account.
                                Expense transactions take money out. Transfers
                                move money between accounts.
                            </p>
                        </div>
                        <div className="flex shrink-0 gap-4 text-sm">
                            <span className="flex items-center gap-1 text-emerald-600">
                                <ArrowDownLeft className="size-4" />
                                Income in
                            </span>
                            <span className="flex items-center gap-1 text-red-600">
                                <ArrowUpRight className="size-4" />
                                Expense out
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <div className="space-y-3">
                    <p className="text-sm font-medium text-muted-foreground">
                        Total balance by currency
                    </p>
                    <div
                        className={cn(
                            'grid gap-4',
                            summary.by_currency.length > 0
                                ? 'sm:grid-cols-2 lg:grid-cols-3'
                                : 'sm:grid-cols-2',
                        )}
                    >
                        {summary.by_currency.map((item) => {
                            const meta = currencyLabels.get(item.currency);

                            return (
                                <Card
                                    key={item.currency}
                                    className="border-0 shadow-sm"
                                >
                                    <CardHeader className="pb-2">
                                        <div className="flex items-center justify-between gap-2">
                                            <CardDescription>
                                                {meta?.label ?? item.currency}
                                            </CardDescription>
                                            <Badge variant="secondary">
                                                {item.currency}
                                            </Badge>
                                        </div>
                                        <CardTitle className="text-2xl">
                                            {formatCurrency(
                                                item.total_balance,
                                                item.currency,
                                            )}
                                        </CardTitle>
                                        <p className="text-xs text-muted-foreground">
                                            {item.account_count}{' '}
                                            {item.account_count === 1
                                                ? 'account'
                                                : 'accounts'}
                                        </p>
                                    </CardHeader>
                                </Card>
                            );
                        })}
                        <Card className="border-0 shadow-sm">
                            <CardHeader className="pb-2">
                                <CardDescription>Active accounts</CardDescription>
                                <CardTitle className="text-2xl">
                                    {summary.account_count}
                                </CardTitle>
                            </CardHeader>
                        </Card>
                    </div>
                </div>

                {accounts.length === 0 ? (
                    <Card className="border-dashed shadow-none">
                        <CardContent className="flex flex-col items-center gap-3 py-12 text-center">
                            <Wallet className="size-10 text-muted-foreground" />
                            <div>
                                <p className="font-medium">No accounts yet</p>
                                <p className="text-sm text-muted-foreground">
                                    Create a checking, savings, or cash account
                                    to start tracking income and expenses.
                                </p>
                            </div>
                            {permissions.create && (
                                <Button
                                    variant="brand"
                                    onClick={() => setCreateOpen(true)}
                                >
                                    <Plus className="size-4" />
                                    Add your first account
                                </Button>
                            )}
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {accounts.map((account) => (
                            <Card
                                key={account.id}
                                className="border-0 shadow-sm"
                            >
                                <CardHeader className="pb-2">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                <TypeIcon type={account.type} />
                                            </div>
                                            <div>
                                                <CardTitle className="text-base">
                                                    {account.name}
                                                </CardTitle>
                                                <CardDescription>
                                                    {account.type_label}
                                                </CardDescription>
                                            </div>
                                        </div>
                                        <Badge variant="secondary">
                                            {account.currency}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <p
                                        className={cn(
                                            'text-2xl font-semibold tabular-nums',
                                            account.balance < 0 &&
                                                'text-red-600',
                                        )}
                                    >
                                        {formatCurrency(
                                            account.balance,
                                            account.currency,
                                        )}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {account.transactions_count}{' '}
                                        {account.transactions_count === 1
                                            ? 'transaction'
                                            : 'transactions'}
                                    </p>
                                    {permissions.update && (
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    setEditing(account)
                                                }
                                            >
                                                <Pencil className="size-4" />
                                                Edit
                                            </Button>
                                            {permissions.delete &&
                                                account.can_delete && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            deleteAccount(
                                                                account,
                                                            )
                                                        }
                                                    >
                                                        <Trash2 className="size-4" />
                                                        Archive
                                                    </Button>
                                                )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
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
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Edit account</DialogTitle>
                    </DialogHeader>
                    {editing && (
                        <Form
                            {...AccountController.update.form(editing.id)}
                            onSuccess={() => setEditing(null)}
                            onError={showValidationErrorToast}
                            className="space-y-4"
                        >
                            {({ processing }) => (
                                <>
                                    <AccountFormFields
                                        accountTypes={accountTypes}
                                        currencies={currencies}
                                        defaultCurrency={workspaceCurrency}
                                        account={editing}
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        Balance changes only through
                                        transactions — not by editing the
                                        account.
                                    </p>
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
