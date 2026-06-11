export type CurrencyOption = {
    code: string;
    symbol: string;
    label: string;
};

export const CURRENCY_OPTIONS: CurrencyOption[] = [
    { code: 'USD', symbol: '$', label: 'US Dollar' },
    { code: 'EUR', symbol: '€', label: 'Euro' },
    { code: 'GBP', symbol: '£', label: 'British Pound' },
    { code: 'BDT', symbol: '৳', label: 'Bangladeshi Taka' },
    { code: 'INR', symbol: '₹', label: 'Indian Rupee' },
    { code: 'JPY', symbol: '¥', label: 'Japanese Yen' },
    { code: 'CAD', symbol: 'CA$', label: 'Canadian Dollar' },
    { code: 'AUD', symbol: 'A$', label: 'Australian Dollar' },
    { code: 'CHF', symbol: 'CHF', label: 'Swiss Franc' },
    { code: 'SGD', symbol: 'S$', label: 'Singapore Dollar' },
];

export function findCurrency(code: string): CurrencyOption {
    return (
        CURRENCY_OPTIONS.find((c) => c.code === code) ?? CURRENCY_OPTIONS[0]
    );
}
