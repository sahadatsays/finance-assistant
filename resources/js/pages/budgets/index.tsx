import { Form, Head, router } from '@inertiajs/react';
import BudgetController from '@/actions/App/Http/Controllers/Finance/BudgetController';
import BudgetUtilizationChart from '@/components/budgets/budget-utilization-chart';
import CategoryProgressChart from '@/components/budgets/category-progress-chart';
import BudgetAlerts from '@/components/dashboard/widgets/budget-alerts';
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
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';
import { exportMethod as exportBudgets } from '@/routes/budgets';
import {
    AlertTriangle,
    Calendar,
    Download,
    Pencil,
    PieChart,
    Plus,
    Trash2,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';

type CategoryLine = {
    category_id: number;
    category: string;
    color: string;
    spent: number;
    budgeted: number;
    percentage: number;
    status: string;
};

type BudgetSummary = {
    budget: {
        id: number;
        name: string;
        period_type: string;
        period_start: string;
        period_end: string;
        amount: number;
    };
    utilization: {
        spent: number;
        budgeted: number;
        remaining: number;
        percentage: number;
        status: string;
    };
    categories: CategoryLine[];
};

type BudgetItem = {
    id: number;
    name: string;
    period_type: string;
    period_start: string;
    period_end: string;
    amount: number;
    is_active: boolean;
    utilization: BudgetSummary['utilization'];
    categories: CategoryLine[];
    lines?: {
        id: number;
        category_id: number;
        category: string;
        color: string;
        amount: number;
    }[];
};

type Props = {
    tenant: { id: number; name: string };
    analytics: {
        monthly: BudgetSummary | null;
        weekly: BudgetSummary | null;
        alerts: {
            id: number;
            name: string;
            type: string;
            category?: string;
            spent: number;
            budgeted: number;
            percentage: number;
            status: string;
        }[];
        trend: {
            period: string;
            spent: number;
            budgeted: number;
            percentage: number;
        }[];
    };
    budgets: BudgetItem[];
    expenseCategories: { id: number; name: string; color: string }[];
    permissions: {
        view: boolean;
        create: boolean;
        update: boolean;
        delete: boolean;
        export: boolean;
    };
};

const statusLabel: Record<string, string> = {
    on_track: 'On Track',
    warning: 'Warning',
    over_budget: 'Over Budget',
};

const statusColor: Record<string, string> = {
    on_track: 'bg-emerald-100 text-emerald-700',
    warning: 'bg-amber-100 text-amber-700',
    over_budget: 'bg-rose-100 text-rose-700',
};

const progressBarColor: Record<string, string> = {
    on_track: 'bg-emerald-500',
    warning: 'bg-amber-500',
    over_budget: 'bg-rose-500',
};

function UtilizationCard({
    title,
    summary,
}: {
    title: string;
    summary: BudgetSummary | null;
}) {
    const { formatCurrency } = useCurrency();

    if (summary === null) {
        return (
            <Card className="border-0 shadow-sm">
                <CardHeader className="pb-2">
                    <CardTitle className="text-base">{title}</CardTitle>
                    <CardDescription>No active budget</CardDescription>
                </CardHeader>
                <CardContent>
                    <p className="text-sm text-muted-foreground">
                        Create a budget to track utilization.
                    </p>
                </CardContent>
            </Card>
        );
    }

    const { budget, utilization } = summary;
    const pct = Math.min(utilization.percentage, 100);

    return (
        <Card className="border-0 shadow-sm">
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between gap-2">
                    <div>
                        <CardTitle className="text-base">{title}</CardTitle>
                        <CardDescription>{budget.name}</CardDescription>
                    </div>
                    <Badge
                        variant="secondary"
                        className={cn(statusColor[utilization.status])}
                    >
                        {statusLabel[utilization.status]}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent className="space-y-3">
                <div className="flex items-baseline justify-between">
                    <span className="text-2xl font-semibold">
                        {utilization.percentage}%
                    </span>
                    <span className="text-sm text-muted-foreground">
                        {formatCurrency(utilization.spent)} of{' '}
                        {formatCurrency(utilization.budgeted)}
                    </span>
                </div>
                <div className="h-2 overflow-hidden rounded-full bg-muted">
                    <div
                        className={cn(
                            'h-full rounded-full transition-all',
                            progressBarColor[utilization.status],
                        )}
                        style={{ width: `${pct}%` }}
                    />
                </div>
                <div className="flex justify-between text-xs text-muted-foreground">
                    <span className="flex items-center gap-1">
                        <Calendar className="size-3" />
                        {budget.period_start} — {budget.period_end}
                    </span>
                    <span>
                        {formatCurrency(utilization.remaining)} left
                    </span>
                </div>
            </CardContent>
        </Card>
    );
}

function BudgetLineFields({
    categories,
    lines,
}: {
    categories: Props['expenseCategories'];
    lines?: BudgetItem['lines'];
}) {
    const [rows, setRows] = useState(
        lines && lines.length > 0
            ? lines.map((l) => ({
                  category_id: String(l.category_id),
                  amount: String(l.amount),
              }))
            : [{ category_id: '', amount: '' }],
    );

    const addRow = () =>
        setRows([...rows, { category_id: '', amount: '' }]);

    const removeRow = (index: number) =>
        setRows(rows.filter((_, i) => i !== index));

    const updateRow = (
        index: number,
        field: 'category_id' | 'amount',
        value: string,
    ) => {
        const next = [...rows];
        next[index] = { ...next[index], [field]: value };
        setRows(next);
    };

    return (
        <div className="space-y-3">
            <Label>Category Budgets</Label>
            {rows.map((row, index) => (
                <div key={index} className="flex gap-2">
                    <select
                        name={`lines[${index}][category_id]`}
                        value={row.category_id}
                        onChange={(e) =>
                            updateRow(index, 'category_id', e.target.value)
                        }
                        className="flex h-9 flex-1 rounded-md border bg-transparent px-3 text-sm"
                        required
                    >
                        <option value="">Select category</option>
                        {categories.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.name}
                            </option>
                        ))}
                    </select>
                    <Input
                        name={`lines[${index}][amount]`}
                        type="number"
                        step="0.01"
                        min="0.01"
                        placeholder="Amount"
                        value={row.amount}
                        onChange={(e) =>
                            updateRow(index, 'amount', e.target.value)
                        }
                        className="w-28"
                        required
                    />
                    {rows.length > 1 && (
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            onClick={() => removeRow(index)}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    )}
                </div>
            ))}
            <Button type="button" variant="outline" size="sm" onClick={addRow}>
                <Plus className="mr-1 size-4" />
                Add Category
            </Button>
        </div>
    );
}

export default function BudgetsIndex({
    tenant,
    analytics,
    budgets,
    expenseCategories,
    permissions,
}: Props) {
    const { formatCurrency } = useCurrency();
    const [editing, setEditing] = useState<BudgetItem | null>(null);

    const alertItems = analytics.alerts.map((a) => ({
        id: a.id,
        name: a.category ? `${a.name} · ${a.category}` : a.name,
        spent: a.spent,
        budgeted: a.budgeted,
        percentage: a.percentage,
        status: a.status,
    }));

    return (
        <>
            <Head title="Budgets" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Budgets</h1>
                        <p className="text-sm text-muted-foreground">
                            Monthly, weekly, and category budgets for{' '}
                            {tenant.name}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {permissions.export && (
                            <Button variant="outline" size="sm" asChild>
                                <a href={exportBudgets.url()}>
                                    <Download className="mr-2 size-4" />
                                    Export Report
                                </a>
                            </Button>
                        )}
                        {permissions.create && (
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button
                                        size="sm"
                                        variant="brand"
                                    >
                                        <Plus className="mr-2 size-4" />
                                        New Budget
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                                    <DialogHeader>
                                        <DialogTitle>Create Budget</DialogTitle>
                                    </DialogHeader>
                                    <Form
                                        {...BudgetController.store.form()}
                                        className="space-y-4"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label>Name</Label>
                                                    <Input
                                                        name="name"
                                                        placeholder="Monthly Budget"
                                                        required
                                                    />
                                                    {errors.name && (
                                                        <p className="text-sm text-rose-600">
                                                            {errors.name}
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>Period Type</Label>
                                                    <select
                                                        name="period_type"
                                                        className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                                        defaultValue="monthly"
                                                    >
                                                        <option value="monthly">
                                                            Monthly
                                                        </option>
                                                        <option value="weekly">
                                                            Weekly
                                                        </option>
                                                    </select>
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>
                                                        Period Start (optional)
                                                    </Label>
                                                    <DatePicker
                                                        name="period_start"
                                                        placeholder="Pick start date"
                                                    />
                                                </div>
                                                <BudgetLineFields
                                                    categories={
                                                        expenseCategories
                                                    }
                                                />
                                                {errors.budget && (
                                                    <p className="text-sm text-rose-600">
                                                        {errors.budget}
                                                    </p>
                                                )}
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                    variant="brand"
                                                >
                                                    Create Budget
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <UtilizationCard
                        title="Monthly Utilization"
                        summary={analytics.monthly}
                    />
                    <UtilizationCard
                        title="Weekly Utilization"
                        summary={analytics.weekly}
                    />
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <TrendingUp className="size-4" />
                                Active Budgets
                            </CardTitle>
                            <CardDescription>
                                {budgets.length} budget
                                {budgets.length !== 1 ? 's' : ''} configured
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap gap-2">
                                {budgets.map((b) => (
                                    <Badge
                                        key={b.id}
                                        variant="secondary"
                                        className="capitalize"
                                    >
                                        <PieChart className="mr-1 size-3" />
                                        {b.name} ({b.period_type})
                                    </Badge>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <BudgetUtilizationChart data={analytics.trend} />
                    <CategoryProgressChart
                        categories={
                            analytics.monthly?.categories ??
                            analytics.weekly?.categories ??
                            []
                        }
                        title="Monthly Category Progress"
                    />
                </div>

                <BudgetAlerts alerts={alertItems} />

                <div className="space-y-4">
                    <h2 className="text-lg font-semibold">All Budgets</h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        {budgets.map((budget) => (
                            <Card
                                key={budget.id}
                                className="border-0 shadow-sm"
                            >
                                <CardHeader>
                                    <div className="flex items-start justify-between gap-2">
                                        <div>
                                            <CardTitle className="text-base">
                                                {budget.name}
                                            </CardTitle>
                                            <CardDescription className="capitalize">
                                                {budget.period_type} ·{' '}
                                                {budget.period_start} —{' '}
                                                {budget.period_end}
                                            </CardDescription>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className={cn(
                                                statusColor[
                                                    budget.utilization.status
                                                ],
                                            )}
                                        >
                                            {budget.utilization.percentage}%
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className={cn(
                                                'h-full rounded-full',
                                                progressBarColor[
                                                    budget.utilization.status
                                                ],
                                            )}
                                            style={{
                                                width: `${Math.min(budget.utilization.percentage, 100)}%`,
                                            }}
                                        />
                                    </div>
                                    <div className="flex justify-between text-sm text-muted-foreground">
                                        <span>
                                            {formatCurrency(
                                                budget.utilization.spent,
                                            )}{' '}
                                            spent
                                        </span>
                                        <span>
                                            {formatCurrency(budget.amount)}{' '}
                                            budgeted
                                        </span>
                                    </div>

                                    <div className="space-y-2">
                                        {budget.categories.map((cat) => (
                                            <div
                                                key={cat.category_id}
                                                className="flex items-center justify-between gap-2 text-sm"
                                            >
                                                <div className="flex items-center gap-2">
                                                    <div
                                                        className="size-2.5 rounded-full"
                                                        style={{
                                                            backgroundColor:
                                                                cat.color,
                                                        }}
                                                    />
                                                    <span>{cat.category}</span>
                                                    {cat.status !==
                                                        'on_track' && (
                                                        <AlertTriangle className="size-3 text-amber-500" />
                                                    )}
                                                </div>
                                                <span className="text-muted-foreground">
                                                    {formatCurrency(cat.spent)}{' '}
                                                    /{' '}
                                                    {formatCurrency(
                                                        cat.budgeted,
                                                    )}
                                                </span>
                                            </div>
                                        ))}
                                    </div>

                                    {permissions.update && (
                                        <div className="flex gap-2 pt-2">
                                            <Dialog
                                                open={
                                                    editing?.id === budget.id
                                                }
                                                onOpenChange={(open) =>
                                                    !open && setEditing(null)
                                                }
                                            >
                                                <DialogTrigger asChild>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            setEditing(budget)
                                                        }
                                                    >
                                                        <Pencil className="mr-1 size-4" />
                                                        Edit
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                                                    <DialogHeader>
                                                        <DialogTitle>
                                                            Edit Budget
                                                        </DialogTitle>
                                                    </DialogHeader>
                                                    {editing?.id ===
                                                        budget.id && (
                                                        <Form
                                                            {...BudgetController.update.form(
                                                                budget.id,
                                                            )}
                                                            className="space-y-4"
                                                        >
                                                            {({
                                                                processing,
                                                                errors,
                                                            }) => (
                                                                <>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Name
                                                                        </Label>
                                                                        <Input
                                                                            name="name"
                                                                            defaultValue={
                                                                                budget.name
                                                                            }
                                                                            required
                                                                        />
                                                                    </div>
                                                                    <BudgetLineFields
                                                                        categories={
                                                                            expenseCategories
                                                                        }
                                                                        lines={
                                                                            budget.lines
                                                                        }
                                                                    />
                                                                    {errors.budget && (
                                                                        <p className="text-sm text-rose-600">
                                                                            {
                                                                                errors.budget
                                                                            }
                                                                        </p>
                                                                    )}
                                                                    <Button
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                        variant="brand"
                                                                    >
                                                                        Save
                                                                        Changes
                                                                    </Button>
                                                                </>
                                                            )}
                                                        </Form>
                                                    )}
                                                </DialogContent>
                                            </Dialog>
                                            {permissions.delete && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="text-rose-600 hover:text-rose-700"
                                                    onClick={() => {
                                                        if (
                                                            confirm(
                                                                `Delete "${budget.name}"?`,
                                                            )
                                                        ) {
                                                            router.delete(
                                                                BudgetController.destroy.url(
                                                                    budget.id,
                                                                ),
                                                            );
                                                        }
                                                    }}
                                                >
                                                    <Trash2 className="mr-1 size-4" />
                                                    Delete
                                                </Button>
                                            )}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
