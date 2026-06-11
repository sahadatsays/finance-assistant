import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useCurrency } from '@/hooks/use-currency';

type DataPoint = { category: string; amount: number; color: string };

export default function CategoryBreakdownChart({
    data,
}: {
    data: DataPoint[];
}) {
    const { formatChartValue } = useCurrency();

    return (
        <Card className="gap-4 border-0 py-4 shadow-sm">
            <CardHeader className="pb-0">
                <CardTitle>Category Breakdown</CardTitle>
                <CardDescription>Expenses by category this month</CardDescription>
            </CardHeader>
            <CardContent className="h-72 pt-0">
                {data.length === 0 ? (
                    <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                        No expense data this month
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie
                                data={data}
                                dataKey="amount"
                                nameKey="category"
                                cx="50%"
                                cy="50%"
                                innerRadius={60}
                                outerRadius={100}
                                paddingAngle={2}
                            >
                                {data.map((entry) => (
                                    <Cell
                                        key={entry.category}
                                        fill={entry.color}
                                    />
                                ))}
                            </Pie>
                            <Tooltip formatter={formatChartValue} />
                        </PieChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
