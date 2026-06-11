import { AlertTriangle, XCircle } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';

type BudgetAlert = {
    id: number;
    name: string;
    spent: number;
    budgeted: number;
    percentage: number;
    status: string;
};

export default function BudgetAlerts({ alerts }: { alerts: BudgetAlert[] }) {
    const { formatCurrency } = useCurrency();

    return (
        <Card className="gap-4 border-0 py-4 shadow-sm">
            <CardHeader className="pb-0">
                <CardTitle>Budget Alerts</CardTitle>
                <CardDescription>Categories needing attention</CardDescription>
            </CardHeader>
            <CardContent className="max-h-96 space-y-3 overflow-y-auto pt-0">
                {alerts.length === 0 ? (
                    <div className="flex items-center gap-2 rounded-lg bg-emerald-500/10 p-3 text-sm text-emerald-700 dark:text-emerald-400">
                        All budgets on track
                    </div>
                ) : (
                    alerts.map((alert) => (
                        <div
                            key={alert.id}
                            className="flex items-start justify-between gap-3 rounded-lg border p-4"
                        >
                            <div className="flex items-start gap-3">
                                <div
                                    className={cn(
                                        'mt-0.5 flex size-8 items-center justify-center rounded-lg',
                                        alert.status === 'over_budget'
                                            ? 'bg-rose-500/10 text-rose-600'
                                            : 'bg-amber-500/10 text-amber-600',
                                    )}
                                >
                                    {alert.status === 'over_budget' ? (
                                        <XCircle className="size-4" />
                                    ) : (
                                        <AlertTriangle className="size-4" />
                                    )}
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        {alert.name}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {formatCurrency(alert.spent)} of{' '}
                                        {formatCurrency(alert.budgeted)}
                                    </p>
                                </div>
                            </div>
                            <Badge
                                variant="secondary"
                                className={cn(
                                    alert.status === 'over_budget'
                                        ? 'bg-rose-100 text-rose-700'
                                        : 'bg-amber-100 text-amber-700',
                                )}
                            >
                                {alert.percentage}%
                            </Badge>
                        </div>
                    ))
                )}
            </CardContent>
        </Card>
    );
}
