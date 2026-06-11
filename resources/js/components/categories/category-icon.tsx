import { resolveCategoryIcon } from '@/lib/category-icons';
import { cn } from '@/lib/utils';

export default function CategoryIcon({
    icon,
    className,
}: {
    icon: string | null | undefined;
    className?: string;
}) {
    const Icon = resolveCategoryIcon(icon);

    return <Icon className={cn('size-4', className)} />;
}
