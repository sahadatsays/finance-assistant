import { usePage } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import { formatCurrency as format } from '@/lib/currency';

type SharedProps = {
    currency?: string;
};

export function useCurrency() {
    const { currency = 'USD' } = usePage<SharedProps>().props;

    const formatCurrency = useCallback(
        (amount: number) => format(amount, currency),
        [currency],
    );

    const formatChartValue = useCallback(
        (value: number | string) => formatCurrency(Number(value)),
        [formatCurrency],
    );

    return useMemo(
        () => ({
            currency,
            formatCurrency,
            formatChartValue,
        }),
        [currency, formatCurrency, formatChartValue],
    );
}
