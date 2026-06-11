import { useState } from 'react';
import CategoryIcon from '@/components/categories/category-icon';
import { Label } from '@/components/ui/label';
import {
    CATEGORY_ICON_OPTIONS,
    type CategoryIconCode,
} from '@/lib/category-icons';
import { cn } from '@/lib/utils';

type Props = {
    name?: string;
    defaultValue?: string | null;
    label?: string;
};

export default function CategoryIconPicker({
    name = 'icon',
    defaultValue = 'tag',
    label = 'Icon',
}: Props) {
    const [selected, setSelected] = useState<string>(defaultValue ?? 'tag');

    return (
        <div className="grid gap-3">
            <Label>{label}</Label>
            <input type="hidden" name={name} value={selected} />
            <div className="flex items-center gap-3 rounded-lg border bg-muted/40 p-3">
                <div className="flex size-12 items-center justify-center rounded-xl bg-violet-600 text-white dark:bg-violet-500">
                    <CategoryIcon icon={selected} className="size-5" />
                </div>
                <div>
                    <p className="text-sm font-medium">Selected icon</p>
                    <p className="text-xs text-muted-foreground">{selected}</p>
                </div>
            </div>
            <div className="grid max-h-40 grid-cols-6 gap-2 overflow-y-auto sm:grid-cols-8">
                {CATEGORY_ICON_OPTIONS.map((icon) => (
                    <button
                        key={icon}
                        type="button"
                        title={icon}
                        onClick={() => setSelected(icon)}
                        className={cn(
                            'flex size-10 items-center justify-center rounded-lg border transition-colors hover:bg-accent',
                            selected === icon &&
                                'border-violet-500 bg-violet-500/10 text-violet-700 dark:text-violet-300',
                        )}
                    >
                        <CategoryIcon icon={icon as CategoryIconCode} />
                    </button>
                ))}
            </div>
        </div>
    );
}
