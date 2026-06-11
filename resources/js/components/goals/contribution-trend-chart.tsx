import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency } from '@/lib/currency';

type DataPoint = { month: string; amount: number };

export default function ContributionTrendChart({
    data,
    title = 'Contribution Trend',
}: {
    data: DataPoint[];
    title?: string;
}) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>
                    Monthly contributions (last 6 months)
                </CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                {data.every((d) => d.amount === 0) ? (
                    <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                        No contributions recorded yet
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <AreaChart data={data}>
                            <CartesianGrid
                                strokeDasharray="3 3"
                                vertical={false}
                            />
                            <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                            <YAxis tick={{ fontSize: 12 }} />
                            <Tooltip
                                formatter={(value: number) => [
                                    formatCurrency(value),
                                    'Contributed',
                                ]}
                            />
                            <Area
                                type="monotone"
                                dataKey="amount"
                                name="Contributed"
                                stroke="#8b5cf6"
                                fill="#8b5cf6"
                                fillOpacity={0.2}
                            />
                        </AreaChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
