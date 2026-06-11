import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
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
import { useCurrency } from '@/hooks/use-currency';

type DataPoint = {
    period: string;
    spent: number;
    budgeted: number;
    percentage: number;
};

export default function BudgetUtilizationChart({ data }: { data: DataPoint[] }) {
    const { formatChartValue } = useCurrency();

    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Budget Utilization Trend</CardTitle>
                <CardDescription>
                    Monthly spent vs budgeted (last 6 periods)
                </CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                {data.length === 0 ? (
                    <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                        No historical budget data yet
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={data}>
                            <CartesianGrid
                                strokeDasharray="3 3"
                                vertical={false}
                            />
                            <XAxis dataKey="period" tick={{ fontSize: 12 }} />
                            <YAxis
                                tick={{ fontSize: 12 }}
                                tickFormatter={formatChartValue}
                            />
                            <Tooltip
                                formatter={(value: number, name: string) => [
                                    formatChartValue(value),
                                    name === 'spent' ? 'Spent' : 'Budgeted',
                                ]}
                            />
                            <Legend />
                            <Bar
                                dataKey="budgeted"
                                name="Budgeted"
                                fill="#8b5cf6"
                                radius={[4, 4, 0, 0]}
                            />
                            <Bar
                                dataKey="spent"
                                name="Spent"
                                fill="#f43f5e"
                                radius={[4, 4, 0, 0]}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
