import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useCurrency } from '@/hooks/use-currency';

type GoalTypeSummary = {
    type: string;
    label: string;
    count: number;
    saved: number;
    target: number;
};

const TYPE_COLORS: Record<string, string> = {
    emergency_fund: '#ef4444',
    travel: '#8b5cf6',
    education: '#3b82f6',
    purchase: '#06b6d4',
    custom: '#10b981',
};

export default function GoalsByTypeChart({
    data,
}: {
    data: GoalTypeSummary[];
}) {
    const { formatChartValue } = useCurrency();

    const chartData = data.map((d) => ({
        name: d.label,
        value: d.saved,
        type: d.type,
    }));

    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Savings by Goal Type</CardTitle>
                <CardDescription>
                    Distribution of saved amounts
                </CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                {chartData.length === 0 ? (
                    <div className="flex h-full items-center justify-center text-sm text-muted-foreground">
                        No savings goals yet
                    </div>
                ) : (
                    <ResponsiveContainer width="100%" height="100%">
                        <PieChart>
                            <Pie
                                data={chartData}
                                dataKey="value"
                                nameKey="name"
                                cx="50%"
                                cy="50%"
                                innerRadius={50}
                                outerRadius={90}
                                paddingAngle={2}
                            >
                                {chartData.map((entry) => (
                                    <Cell
                                        key={entry.type}
                                        fill={
                                            TYPE_COLORS[entry.type] ?? '#94a3b8'
                                        }
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
