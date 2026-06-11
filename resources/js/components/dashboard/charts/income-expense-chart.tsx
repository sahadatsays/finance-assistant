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

type DataPoint = { month: string; income: number; expense: number };

export default function IncomeExpenseChart({ data }: { data: DataPoint[] }) {
    const { formatChartValue } = useCurrency();

    return (
        <Card className="gap-4 border-0 py-4 shadow-sm">
            <CardHeader className="pb-0">
                <CardTitle>Income vs Expense</CardTitle>
                <CardDescription>Monthly comparison (last 6 months)</CardDescription>
            </CardHeader>
            <CardContent className="h-72 pt-0">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                        <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                        <YAxis
                            tick={{ fontSize: 12 }}
                            tickFormatter={formatChartValue}
                        />
                        <Tooltip formatter={formatChartValue} />
                        <Legend />
                        <Bar
                            dataKey="income"
                            name="Income"
                            fill="#10b981"
                            radius={[4, 4, 0, 0]}
                        />
                        <Bar
                            dataKey="expense"
                            name="Expense"
                            fill="#f43f5e"
                            radius={[4, 4, 0, 0]}
                        />
                    </BarChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}
