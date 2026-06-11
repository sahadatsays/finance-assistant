import { Head } from '@inertiajs/react';
import {
    Landmark,
    PiggyBank,
    Target,
    TrendingDown,
    TrendingUp,
    Wallet,
} from 'lucide-react';
import StatCard from '@/components/admin/stat-card';
import CategoryBreakdownChart from '@/components/dashboard/charts/category-breakdown-chart';
import IncomeExpenseChart from '@/components/dashboard/charts/income-expense-chart';
import MonthlyTrendChart from '@/components/dashboard/charts/monthly-trend-chart';
import TenantSwitcher from '@/components/dashboard/tenant-switcher';
import BudgetAlerts from '@/components/dashboard/widgets/budget-alerts';
import RecentTransactions from '@/components/dashboard/widgets/recent-transactions';
import SavingsGoals from '@/components/dashboard/widgets/savings-goals';
import { useCurrency } from '@/hooks/use-currency';
import { dashboard } from '@/routes';

type TenantOption = { id: number; name: string; slug: string };

type Metrics = {
    total_income: number;
    total_expense: number;
    total_savings: number;
    budget_status: {
        spent: number;
        budgeted: number;
        percentage: number;
        status: string;
    };
    net_worth: number;
};

type Charts = {
    income_vs_expense: { month: string; income: number; expense: number }[];
    category_breakdown: { category: string; amount: number; color: string }[];
    monthly_trend: { month: string; net: number }[];
};

type Widgets = {
    recent_transactions: {
        id: number;
        notes: string | null;
        amount: number;
        type: string;
        category: string | null;
        occurred_at: string;
    }[];
    budget_alerts: {
        id: number;
        name: string;
        spent: number;
        budgeted: number;
        percentage: number;
        status: string;
    }[];
    savings_goals: {
        id: number;
        name: string;
        current_amount: number;
        target_amount: number;
        percentage: number;
        color: string;
        target_date: string | null;
    }[];
};

type Props = {
    tenant: TenantOption | null;
    tenants: TenantOption[];
    metrics: Metrics | null;
    charts: Charts | null;
    widgets: Widgets | null;
};

const budgetStatusLabel: Record<string, string> = {
    on_track: 'On Track',
    warning: 'Warning',
    over_budget: 'Over Budget',
};

const budgetStatusColor: Record<string, string> = {
    on_track: 'bg-emerald-100 text-emerald-700',
    warning: 'bg-amber-100 text-amber-700',
    over_budget: 'bg-rose-100 text-rose-700',
};

export default function Dashboard({
    tenant,
    tenants,
    metrics,
    charts,
    widgets,
}: Props) {
    const { formatCurrency } = useCurrency();

    if (tenant === null || metrics === null || charts === null || widgets === null) {
        return (
            <>
                <Head title="Dashboard" />
                <div className="flex flex-col items-center justify-center gap-4 py-24 text-center">
                    <Wallet className="size-12 text-muted-foreground" />
                    <h1 className="text-xl font-semibold">No workspace yet</h1>
                    <p className="max-w-md text-sm text-muted-foreground">
                        You are not a member of any active tenant. Contact your
                        administrator or create a workspace to get started.
                    </p>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Dashboard" />

            <div className="space-y-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Dashboard</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Your financial overview
                        </p>
                    </div>
                    <TenantSwitcher tenant={tenant} tenants={tenants} />
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <StatCard
                        title="Total Income"
                        value={formatCurrency(metrics.total_income)}
                        subtitle="This month"
                        icon={TrendingUp}
                        color="green"
                    />
                    <StatCard
                        title="Total Expense"
                        value={formatCurrency(metrics.total_expense)}
                        subtitle="This month"
                        icon={TrendingDown}
                        color="red"
                    />
                    <StatCard
                        title="Total Savings"
                        value={formatCurrency(metrics.total_savings)}
                        subtitle="Savings + goals"
                        icon={PiggyBank}
                        color="cyan"
                    />
                    <StatCard
                        title="Budget Status"
                        value={`${metrics.budget_status.percentage}%`}
                        subtitle={`${formatCurrency(metrics.budget_status.spent)} of ${formatCurrency(metrics.budget_status.budgeted)}`}
                        badge={
                            budgetStatusLabel[metrics.budget_status.status]
                        }
                        badgeClassName={
                            budgetStatusColor[metrics.budget_status.status]
                        }
                        icon={Target}
                        color="orange"
                    />
                    <StatCard
                        title="Net Worth"
                        value={formatCurrency(metrics.net_worth)}
                        subtitle="All accounts"
                        icon={Landmark}
                        color="violet"
                    />
                </div>

                <section className="space-y-4">
                    <h2 className="text-lg font-semibold">Analytics</h2>
                    <div className="grid gap-4 lg:grid-cols-2">
                        <IncomeExpenseChart data={charts.income_vs_expense} />
                        <CategoryBreakdownChart
                            data={charts.category_breakdown}
                        />
                        <MonthlyTrendChart data={charts.monthly_trend} />
                        <SavingsGoals goals={widgets.savings_goals} />
                    </div>
                </section>

                <section className="space-y-4">
                    <h2 className="text-lg font-semibold">Activity</h2>
                    <div className="grid gap-4 lg:grid-cols-2 lg:items-start">
                        <RecentTransactions
                            transactions={widgets.recent_transactions}
                        />
                        <BudgetAlerts alerts={widgets.budget_alerts} />
                    </div>
                </section>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
