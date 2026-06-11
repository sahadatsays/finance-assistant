import { usePage } from '@inertiajs/react';
import { formatCurrency as format } from '@/lib/currency';

type SharedProps = {
    currency?: string;
};

export function useCurrency() {
    const { currency = 'USD' } = usePage<SharedProps>().props;

    return {
        currency,
        formatCurrency: (amount: number) => format(amount, currency),
    };
}
