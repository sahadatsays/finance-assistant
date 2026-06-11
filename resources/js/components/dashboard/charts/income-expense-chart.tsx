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

type DataPoint = { month: string; income: number; expense: number };

export default function IncomeExpenseChart({ data }: { data: DataPoint[] }) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Income vs Expense</CardTitle>
                <CardDescription>Monthly comparison (last 6 months)</CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                    <BarChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                        <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                        <YAxis tick={{ fontSize: 12 }} />
                        <Tooltip />
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
