import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type DataPoint = { status: string; label: string; count: number };

const COLORS = ['#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'];

export default function TenantStatisticsChart({
    data,
}: {
    data: DataPoint[];
}) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Tenant Statistics</CardTitle>
                <CardDescription>Distribution by status</CardDescription>
            </CardHeader>
            <CardContent className="h-72">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={data}
                            dataKey="count"
                            nameKey="label"
                            cx="50%"
                            cy="50%"
                            innerRadius={60}
                            outerRadius={90}
                            paddingAngle={2}
                        >
                            {data.map((entry, index) => (
                                <Cell
                                    key={entry.status}
                                    fill={COLORS[index % COLORS.length]}
                                />
                            ))}
                        </Pie>
                        <Tooltip />
                    </PieChart>
                </ResponsiveContainer>
                <div className="mt-2 flex flex-wrap justify-center gap-3">
                    {data.map((item, index) => (
                        <div
                            key={item.status}
                            className="flex items-center gap-1.5 text-xs"
                        >
                            <span
                                className="size-2 rounded-full"
                                style={{
                                    backgroundColor:
                                        COLORS[index % COLORS.length],
                                }}
                            />
                            {item.label}: {item.count}
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
