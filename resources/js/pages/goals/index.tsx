import { Form, Head, router } from '@inertiajs/react';
import GoalController from '@/actions/App/Http/Controllers/Finance/GoalController';
import ContributionTrendChart from '@/components/goals/contribution-trend-chart';
import GoalsByTypeChart from '@/components/goals/goals-by-type-chart';
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
import { formatCurrency } from '@/lib/currency';
import { cn } from '@/lib/utils';
import { exportMethod as exportGoals } from '@/routes/goals';
import {
    Calendar,
    Download,
    GraduationCap,
    HeartPulse,
    Landmark,
    Pencil,
    Plane,
    Plus,
    ShoppingBag,
    Target,
    Trash2,
    TrendingUp,
} from 'lucide-react';
import { useState } from 'react';

type GoalType = { value: string; label: string; color: string };

type Contribution = {
    id: number;
    amount: number;
    notes: string | null;
    contributed_at: string;
};

type GoalItem = {
    id: number;
    name: string;
    type: string;
    type_label: string;
    target_amount: number;
    current_amount: number;
    target_date: string | null;
    color: string;
    progress: {
        current: number;
        target: number;
        remaining: number;
        percentage: number;
        status: string;
    };
    forecast: {
        remaining: number;
        days_remaining: number | null;
        required_monthly: number | null;
        average_monthly: number | null;
        projected_completion: string | null;
        is_behind: boolean;
    };
    contributions?: Contribution[];
    contribution_trend?: { month: string; amount: number }[];
};

type Props = {
    tenant: { id: number; name: string };
    analytics: {
        summary: {
            total_saved: number;
            total_target: number;
            active_count: number;
            completed_count: number;
        };
        by_type: {
            type: string;
            label: string;
            count: number;
            saved: number;
            target: number;
        }[];
        trend: { month: string; amount: number }[];
        goals: GoalItem[];
    };
    goals: GoalItem[];
    goalTypes: GoalType[];
    permissions: {
        view: boolean;
        create: boolean;
        update: boolean;
        delete: boolean;
        contribute: boolean;
        export: boolean;
    };
};

const statusLabel: Record<string, string> = {
    on_track: 'On Track',
    behind: 'Behind Schedule',
    completed: 'Completed',
};

const statusColor: Record<string, string> = {
    on_track: 'bg-emerald-100 text-emerald-700',
    behind: 'bg-amber-100 text-amber-700',
    completed: 'bg-violet-100 text-violet-700',
};

const typeIcons: Record<string, typeof Target> = {
    emergency_fund: HeartPulse,
    travel: Plane,
    education: GraduationCap,
    purchase: ShoppingBag,
    custom: Target,
};

function TypeIcon({ type }: { type: string }) {
    const Icon = typeIcons[type] ?? Target;
    return <Icon className="size-4" />;
}

function SummaryCard({
    title,
    value,
    subtitle,
    icon: Icon,
}: {
    title: string;
    value: string;
    subtitle: string;
    icon: typeof Target;
}) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                    <CardTitle className="text-sm font-medium text-muted-foreground">
                        {title}
                    </CardTitle>
                    <Icon className="size-4 text-violet-600" />
                </div>
            </CardHeader>
            <CardContent>
                <p className="text-2xl font-semibold">{value}</p>
                <p className="text-xs text-muted-foreground">{subtitle}</p>
            </CardContent>
        </Card>
    );
}

export default function GoalsIndex({
    tenant,
    analytics,
    goals,
    goalTypes,
    permissions,
}: Props) {
    const [editing, setEditing] = useState<GoalItem | null>(null);
    const [contributing, setContributing] = useState<GoalItem | null>(null);

    const overallPct =
        analytics.summary.total_target > 0
            ? Math.round(
                  (analytics.summary.total_saved /
                      analytics.summary.total_target) *
                      100,
              )
            : 0;

    return (
        <>
            <Head title="Savings Goals" />

            <div className="space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Savings Goals
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Track targets, contributions, and forecasts for{' '}
                            {tenant.name}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {permissions.export && (
                            <Button variant="outline" size="sm" asChild>
                                <a href={exportGoals.url()}>
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
                                        className="bg-violet-600 hover:bg-violet-700"
                                    >
                                        <Plus className="mr-2 size-4" />
                                        New Goal
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="sm:max-w-lg">
                                    <DialogHeader>
                                        <DialogTitle>
                                            Create Savings Goal
                                        </DialogTitle>
                                    </DialogHeader>
                                    <Form
                                        {...GoalController.store.form()}
                                        className="space-y-4"
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <Label>Name</Label>
                                                    <Input
                                                        name="name"
                                                        placeholder="Emergency Fund"
                                                        required
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>Goal Type</Label>
                                                    <select
                                                        name="type"
                                                        className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                                        defaultValue="custom"
                                                    >
                                                        {goalTypes.map((t) => (
                                                            <option
                                                                key={t.value}
                                                                value={t.value}
                                                            >
                                                                {t.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>Target Amount</Label>
                                                    <Input
                                                        name="target_amount"
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                        required
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>Target Date</Label>
                                                    <Input
                                                        name="target_date"
                                                        type="date"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>Color</Label>
                                                    <Input
                                                        name="color"
                                                        type="color"
                                                        defaultValue="#10b981"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <Label>
                                                        Initial Contribution
                                                        (optional)
                                                    </Label>
                                                    <Input
                                                        name="initial_contribution"
                                                        type="number"
                                                        step="0.01"
                                                        min="0.01"
                                                    />
                                                </div>
                                                {errors.goal && (
                                                    <p className="text-sm text-rose-600">
                                                        {errors.goal}
                                                    </p>
                                                )}
                                                <Button
                                                    type="submit"
                                                    disabled={processing}
                                                    className="bg-violet-600 hover:bg-violet-700"
                                                >
                                                    Create Goal
                                                </Button>
                                            </>
                                        )}
                                    </Form>
                                </DialogContent>
                            </Dialog>
                        )}
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <SummaryCard
                        title="Total Saved"
                        value={formatCurrency(analytics.summary.total_saved)}
                        subtitle={`${overallPct}% of ${formatCurrency(analytics.summary.total_target)} target`}
                        icon={Landmark}
                    />
                    <SummaryCard
                        title="Active Goals"
                        value={String(analytics.summary.active_count)}
                        subtitle={`${analytics.summary.completed_count} completed`}
                        icon={Target}
                    />
                    <SummaryCard
                        title="Remaining"
                        value={formatCurrency(
                            Math.max(
                                analytics.summary.total_target -
                                    analytics.summary.total_saved,
                                0,
                            ),
                        )}
                        subtitle="Across all goals"
                        icon={TrendingUp}
                    />
                    <SummaryCard
                        title="Goal Types"
                        value={String(analytics.by_type.length)}
                        subtitle="Categories in use"
                        icon={Calendar}
                    />
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    <ContributionTrendChart data={analytics.trend} />
                    <GoalsByTypeChart data={analytics.by_type} />
                </div>

                <div className="space-y-4">
                    <h2 className="text-lg font-semibold">Your Goals</h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        {goals.map((goal) => (
                            <Card
                                key={goal.id}
                                className="border-0 shadow-sm"
                            >
                                <CardHeader>
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex items-center gap-3">
                                            <div
                                                className="flex size-10 items-center justify-center rounded-xl text-white"
                                                style={{
                                                    backgroundColor:
                                                        goal.color,
                                                }}
                                            >
                                                <TypeIcon type={goal.type} />
                                            </div>
                                            <div>
                                                <CardTitle className="text-base">
                                                    {goal.name}
                                                </CardTitle>
                                                <CardDescription>
                                                    {goal.type_label}
                                                </CardDescription>
                                            </div>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className={cn(
                                                statusColor[
                                                    goal.progress.status
                                                ],
                                            )}
                                        >
                                            {statusLabel[goal.progress.status]}
                                        </Badge>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex items-baseline justify-between">
                                        <span className="text-xl font-semibold">
                                            {goal.progress.percentage}%
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {formatCurrency(
                                                goal.current_amount,
                                            )}{' '}
                                            /{' '}
                                            {formatCurrency(
                                                goal.target_amount,
                                            )}
                                        </span>
                                    </div>
                                    <div className="h-2 overflow-hidden rounded-full bg-muted">
                                        <div
                                            className="h-full rounded-full transition-all"
                                            style={{
                                                width: `${goal.progress.percentage}%`,
                                                backgroundColor: goal.color,
                                            }}
                                        />
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p className="text-muted-foreground">
                                                Remaining
                                            </p>
                                            <p className="font-medium">
                                                {formatCurrency(
                                                    goal.progress.remaining,
                                                )}
                                            </p>
                                        </div>
                                        {goal.forecast.required_monthly !==
                                            null && (
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Required / month
                                                </p>
                                                <p className="font-medium">
                                                    {formatCurrency(
                                                        goal.forecast
                                                            .required_monthly,
                                                    )}
                                                </p>
                                            </div>
                                        )}
                                        {goal.forecast.average_monthly !==
                                            null && (
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Avg / month
                                                </p>
                                                <p className="font-medium">
                                                    {formatCurrency(
                                                        goal.forecast
                                                            .average_monthly,
                                                    )}
                                                </p>
                                            </div>
                                        )}
                                        {goal.forecast.projected_completion && (
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Projected
                                                </p>
                                                <p className="font-medium">
                                                    {new Date(
                                                        goal.forecast.projected_completion,
                                                    ).toLocaleDateString()}
                                                </p>
                                            </div>
                                        )}
                                        {goal.target_date && (
                                            <div>
                                                <p className="text-muted-foreground">
                                                    Target date
                                                </p>
                                                <p className="font-medium">
                                                    {new Date(
                                                        goal.target_date,
                                                    ).toLocaleDateString()}
                                                </p>
                                            </div>
                                        )}
                                    </div>

                                    {goal.contributions &&
                                        goal.contributions.length > 0 && (
                                            <div className="space-y-2">
                                                <p className="text-xs font-medium text-muted-foreground uppercase">
                                                    Recent Contributions
                                                </p>
                                                {goal.contributions.map(
                                                    (c) => (
                                                        <div
                                                            key={c.id}
                                                            className="flex items-center justify-between text-sm"
                                                        >
                                                            <span>
                                                                {formatCurrency(
                                                                    c.amount,
                                                                )}
                                                                {c.notes && (
                                                                    <span className="ml-1 text-muted-foreground">
                                                                        ·{' '}
                                                                        {
                                                                            c.notes
                                                                        }
                                                                    </span>
                                                                )}
                                                            </span>
                                                            <span className="text-xs text-muted-foreground">
                                                                {new Date(
                                                                    c.contributed_at,
                                                                ).toLocaleDateString()}
                                                            </span>
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        )}

                                    <div className="flex flex-wrap gap-2 pt-2">
                                        {permissions.contribute && (
                                            <Dialog
                                                open={
                                                    contributing?.id ===
                                                    goal.id
                                                }
                                                onOpenChange={(open) =>
                                                    !open &&
                                                    setContributing(null)
                                                }
                                            >
                                                <DialogTrigger asChild>
                                                    <Button
                                                        size="sm"
                                                        className="bg-violet-600 hover:bg-violet-700"
                                                        onClick={() =>
                                                            setContributing(
                                                                goal,
                                                            )
                                                        }
                                                    >
                                                        <Plus className="mr-1 size-4" />
                                                        Contribute
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>
                                                            Add Contribution —{' '}
                                                            {goal.name}
                                                        </DialogTitle>
                                                    </DialogHeader>
                                                    {contributing?.id ===
                                                        goal.id && (
                                                        <Form
                                                            {...GoalController.contribute.form(
                                                                goal.id,
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
                                                                            Amount
                                                                        </Label>
                                                                        <Input
                                                                            name="amount"
                                                                            type="number"
                                                                            step="0.01"
                                                                            min="0.01"
                                                                            required
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Notes
                                                                        </Label>
                                                                        <Input
                                                                            name="notes"
                                                                            placeholder="Optional"
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Date
                                                                        </Label>
                                                                        <Input
                                                                            name="contributed_at"
                                                                            type="date"
                                                                        />
                                                                    </div>
                                                                    {errors.contribution && (
                                                                        <p className="text-sm text-rose-600">
                                                                            {
                                                                                errors.contribution
                                                                            }
                                                                        </p>
                                                                    )}
                                                                    <Button
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                        className="bg-violet-600 hover:bg-violet-700"
                                                                    >
                                                                        Add
                                                                        Contribution
                                                                    </Button>
                                                                </>
                                                            )}
                                                        </Form>
                                                    )}
                                                </DialogContent>
                                            </Dialog>
                                        )}
                                        {permissions.update && (
                                            <Dialog
                                                open={
                                                    editing?.id === goal.id
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
                                                            setEditing(goal)
                                                        }
                                                    >
                                                        <Pencil className="mr-1 size-4" />
                                                        Edit
                                                    </Button>
                                                </DialogTrigger>
                                                <DialogContent>
                                                    <DialogHeader>
                                                        <DialogTitle>
                                                            Edit Goal
                                                        </DialogTitle>
                                                    </DialogHeader>
                                                    {editing?.id ===
                                                        goal.id && (
                                                        <Form
                                                            {...GoalController.update.form(
                                                                goal.id,
                                                            )}
                                                            className="space-y-4"
                                                        >
                                                            {({ processing }) => (
                                                                <>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Name
                                                                        </Label>
                                                                        <Input
                                                                            name="name"
                                                                            defaultValue={
                                                                                goal.name
                                                                            }
                                                                            required
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Type
                                                                        </Label>
                                                                        <select
                                                                            name="type"
                                                                            defaultValue={
                                                                                goal.type
                                                                            }
                                                                            className="flex h-9 w-full rounded-md border bg-transparent px-3 text-sm"
                                                                        >
                                                                            {goalTypes.map(
                                                                                (
                                                                                    t,
                                                                                ) => (
                                                                                    <option
                                                                                        key={
                                                                                            t.value
                                                                                        }
                                                                                        value={
                                                                                            t.value
                                                                                        }
                                                                                    >
                                                                                        {
                                                                                            t.label
                                                                                        }
                                                                                    </option>
                                                                                ),
                                                                            )}
                                                                        </select>
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Target
                                                                            Amount
                                                                        </Label>
                                                                        <Input
                                                                            name="target_amount"
                                                                            type="number"
                                                                            step="0.01"
                                                                            defaultValue={
                                                                                goal.target_amount
                                                                            }
                                                                            required
                                                                        />
                                                                    </div>
                                                                    <div className="grid gap-2">
                                                                        <Label>
                                                                            Target
                                                                            Date
                                                                        </Label>
                                                                        <Input
                                                                            name="target_date"
                                                                            type="date"
                                                                            defaultValue={
                                                                                goal.target_date ??
                                                                                ''
                                                                            }
                                                                        />
                                                                    </div>
                                                                    <Button
                                                                        type="submit"
                                                                        disabled={
                                                                            processing
                                                                        }
                                                                        className="bg-violet-600 hover:bg-violet-700"
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
                                        )}
                                        {permissions.delete && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="text-rose-600"
                                                onClick={() => {
                                                    if (
                                                        confirm(
                                                            `Delete "${goal.name}"?`,
                                                        )
                                                    ) {
                                                        router.delete(
                                                            GoalController.destroy.url(
                                                                goal.id,
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
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
