import { format, parseISO } from 'date-fns';
import { CalendarIcon } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type Props = {
    name: string;
    defaultValue?: string;
    required?: boolean;
    placeholder?: string;
    className?: string;
};

export function DatePicker({
    name,
    defaultValue,
    required = false,
    placeholder = 'Pick a date',
    className,
}: Props) {
    const initial = defaultValue ? parseISO(defaultValue) : undefined;
    const [date, setDate] = useState<Date | undefined>(initial);
    const [open, setOpen] = useState(false);

    const value = date ? format(date, 'yyyy-MM-dd') : '';

    return (
        <div className={className}>
            <input type="hidden" name={name} value={value} required={required} />
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <Button
                        type="button"
                        variant="outline"
                        className={cn(
                            'w-full justify-start text-left font-normal',
                            !date && 'text-muted-foreground',
                        )}
                    >
                        <CalendarIcon className="mr-2 size-4" />
                        {date ? format(date, 'PPP') : placeholder}
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="single"
                        selected={date}
                        onSelect={(selected) => {
                            setDate(selected);
                            setOpen(false);
                        }}
                        initialFocus
                    />
                </PopoverContent>
            </Popover>
        </div>
    );
}
