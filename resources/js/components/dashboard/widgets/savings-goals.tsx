import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatCurrency } from '@/lib/currency';

type SavingsGoal = {
    id: number;
    name: string;
    current_amount: number;
    target_amount: number;
    percentage: number;
    color: string;
    target_date: string | null;
};

export default function SavingsGoals({ goals }: { goals: SavingsGoal[] }) {
    return (
        <Card className="border-0 shadow-sm">
            <CardHeader>
                <CardTitle>Savings Goals</CardTitle>
                <CardDescription>Track your progress</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
                {goals.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No savings goals yet
                    </p>
                ) : (
                    goals.map((goal) => (
                        <div key={goal.id} className="space-y-2">
                            <div className="flex items-center justify-between">
                                <p className="text-sm font-medium">
                                    {goal.name}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    {formatCurrency(goal.current_amount)} /{' '}
                                    {formatCurrency(goal.target_amount)}
                                </p>
                            </div>
                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full transition-all"
                                    style={{
                                        width: `${goal.percentage}%`,
                                        backgroundColor: goal.color,
                                    }}
                                />
                            </div>
                            <div className="flex justify-between text-xs text-muted-foreground">
                                <span>{goal.percentage}% complete</span>
                                {goal.target_date && (
                                    <span>
                                        Target:{' '}
                                        {new Date(
                                            goal.target_date,
                                        ).toLocaleDateString()}
                                    </span>
                                )}
                            </div>
                        </div>
                    ))
                )}
            </CardContent>
        </Card>
    );
}
