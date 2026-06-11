import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type Props = {
    title: string;
    value: string | number;
    subtitle?: string;
    badge?: string;
    badgeClassName?: string;
    icon: LucideIcon;
    color?: 'violet' | 'green' | 'orange' | 'blue' | 'red' | 'cyan';
};

const colorMap = {
    violet: 'bg-violet-500/10 text-violet-600',
    green: 'bg-emerald-500/10 text-emerald-600',
    orange: 'bg-orange-500/10 text-orange-600',
    blue: 'bg-blue-500/10 text-blue-600',
    red: 'bg-rose-500/10 text-rose-600',
    cyan: 'bg-cyan-500/10 text-cyan-600',
};

export default function StatCard({
    title,
    value,
    subtitle,
    badge,
    badgeClassName,
    icon: Icon,
    color = 'violet',
}: Props) {
    return (
        <Card className="border-0 shadow-sm">
            <CardContent className="flex items-center justify-between gap-4 p-5">
                <div className="min-w-0 flex-1">
                    <p className="text-sm text-muted-foreground">{title}</p>
                    <p className="mt-1 text-2xl font-semibold">{value}</p>
                    {subtitle && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            {subtitle}
                        </p>
                    )}
                    {badge && (
                        <span
                            className={cn(
                                'mt-2 inline-flex rounded-md px-2 py-0.5 text-xs font-medium',
                                badgeClassName,
                            )}
                        >
                            {badge}
                        </span>
                    )}
                </div>
                <div
                    className={cn(
                        'flex size-12 items-center justify-center rounded-xl',
                        colorMap[color],
                    )}
                >
                    <Icon className="size-6" />
                </div>
            </CardContent>
        </Card>
    );
}
