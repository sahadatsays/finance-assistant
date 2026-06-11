import {
    CartesianGrid,
    Line,
    LineChart,
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

type DataPoint = { month: string; net: number };

export default function MonthlyTrendChart({ data }: { data: DataPoint[] }) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Monthly Trend</CardTitle>
                <CardDescription>Net income over time</CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                    <LineChart data={data}>
                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                        <XAxis dataKey="month" tick={{ fontSize: 12 }} />
                        <YAxis tick={{ fontSize: 12 }} />
                        <Tooltip />
                        <Line
                            type="monotone"
                            dataKey="net"
                            stroke="#7c3aed"
                            strokeWidth={2}
                            dot={{ fill: '#7c3aed', r: 4 }}
                        />
                    </LineChart>
                </ResponsiveContainer>
            </CardContent>
        </Card>
    );
}
