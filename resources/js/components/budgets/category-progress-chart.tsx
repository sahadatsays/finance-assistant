import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
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

type CategoryProgress = {
    category_id: number;
    category: string;
    color: string;
    spent: number;
    budgeted: number;
    percentage: number;
    status: string;
};

export default function CategoryProgressChart({
    categories,
    title = 'Category Budget Progress',
}: {
    categories: CategoryProgress[];
    title?: string;
}) {
    const { formatChartValue } = useCurrency();

    const data = categories.map((c) => ({
        name: c.category,
        spent: c.spent,
        budgeted: c.budgeted,
        color: c.color,
        percentage: c.percentage,
    }));

    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>
                    Spent vs budgeted by category
                </CardDescription>
            </CardHeader>
            <CardContent className="h-80">
                {data.length === 0 ? (
                    <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                        No category budgets configured
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <BarChart data={data} layout="vertical">
                            <CartesianGrid
                                strokeDasharray="3 3"
                                horizontal={false}
                            />
                            <XAxis
                                type="number"
                                tick={{ fontSize: 12 }}
                                tickFormatter={formatChartValue}
                            />
                            <YAxis
                                type="category"
                                dataKey="name"
                                width={100}
                                tick={{ fontSize: 11 }}
                            />
                            <Tooltip
                                formatter={(value: number, name: string) => [
                                    formatChartValue(value),
                                    name === 'spent' ? 'Spent' : 'Budgeted',
                                ]}
                                labelFormatter={(label) => label}
                            />
                            <Bar
                                dataKey="budgeted"
                                name="Budgeted"
                                fill="#e2e8f0"
                                radius={[0, 4, 4, 0]}
                                barSize={12}
                            />
                            <Bar
                                dataKey="spent"
                                name="Spent"
                                radius={[0, 4, 4, 0]}
                                barSize={12}
                            >
                                {data.map((entry) => (
                                    <Cell
                                        key={entry.name}
                                        fill={entry.color}
                                    />
                                ))}
                            </Bar>
                        </BarChart>
                    </ResponsiveContainer>
                )}
            </CardContent>
        </Card>
    );
}
