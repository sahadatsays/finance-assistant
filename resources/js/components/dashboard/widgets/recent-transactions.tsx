import { ArrowDownLeft, ArrowUpRight } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency } from '@/lib/currency';
import { cn } from '@/lib/utils';

type Transaction = {
    id: number;
    notes: string | null;
    amount: number;
    type: string;
    category: string | null;
    occurred_at: string;
};

export default function RecentTransactions({
    transactions,
}: {
    transactions: Transaction[];
}) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Recent Transactions</CardTitle>
                <CardDescription>Latest activity</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
                {transactions.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No transactions yet
                    </p>
                ) : (
                    transactions.map((tx) => (
                        <div
                            key={tx.id}
                            className="flex items-center justify-between gap-3 rounded-lg border p-3"
                        >
                            <div className="flex items-center gap-3">
                                <div
                                    className={cn(
                                        'flex size-9 items-center justify-center rounded-lg',
                                        tx.type === 'income'
                                            ? 'bg-emerald-500/10 text-emerald-600'
                                            : 'bg-rose-500/10 text-rose-600',
                                    )}
                                >
                                    {tx.type === 'income' ? (
                                        <ArrowDownLeft className="size-4" />
                                    ) : (
                                        <ArrowUpRight className="size-4" />
                                    )}
                                </div>
                                <div>
                                    <p className="text-sm font-medium">
                                        {tx.notes ?? 'No notes'}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {tx.category ?? 'Uncategorized'} ·{' '}
                                        {new Date(
                                            tx.occurred_at,
                                        ).toLocaleDateString()}
                                    </p>
                                </div>
                            </div>
                            <span
                                className={cn(
                                    'text-sm font-semibold',
                                    tx.type === 'income'
                                        ? 'text-emerald-600'
                                        : 'text-rose-600',
                                )}
                            >
                                {tx.type === 'income' ? '+' : '-'}
                                {formatCurrency(tx.amount)}
                            </span>
                        </div>
                    ))
                )}
            </CardContent>
        </Card>
    );
}
