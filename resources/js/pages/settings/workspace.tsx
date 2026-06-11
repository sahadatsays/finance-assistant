import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import WorkspaceSettingsController from '@/actions/App/Http/Controllers/Settings/WorkspaceSettingsController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { findCurrency } from '@/lib/currencies';
import { cn } from '@/lib/utils';

type Currency = {
    code: string;
    symbol: string;
    label: string;
};

type Props = {
    tenant: {
        id: number;
        name: string;
        settings: { currency?: string };
    };
    currencies: Currency[];
};

export default function WorkspaceSettings({ tenant, currencies }: Props) {
    const [selectedCurrency, setSelectedCurrency] = useState(
        tenant.settings.currency ?? 'USD',
    );

    return (
        <>
            <Head title="Workspace settings" />

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Workspace"
                    description={`Currency and preferences for ${tenant.name}`}
                />

                <Form
                    {...WorkspaceSettingsController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-3">
                                <Label>Currency</Label>
                                <p className="text-sm text-muted-foreground">
                                    Choose the currency symbol used across
                                    dashboards, budgets, and transactions.
                                </p>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    {currencies.map((currency) => (
                                        <label
                                            key={currency.code}
                                            className={cn(
                                                'flex cursor-pointer items-center gap-3 rounded-lg border p-3 transition-colors hover:bg-accent',
                                                selectedCurrency === currency.code &&
                                                    'border-violet-500 bg-violet-500/10 dark:border-violet-400',
                                            )}
                                        >
                                            <input
                                                type="radio"
                                                name="settings[currency]"
                                                value={currency.code}
                                                defaultChecked={
                                                    selectedCurrency ===
                                                    currency.code
                                                }
                                                onChange={() =>
                                                    setSelectedCurrency(
                                                        currency.code,
                                                    )
                                                }
                                                className="sr-only"
                                            />
                                            <span className="flex size-10 items-center justify-center rounded-lg bg-muted text-lg font-semibold">
                                                {currency.symbol}
                                            </span>
                                            <span>
                                                <span className="block text-sm font-medium">
                                                    {currency.label}
                                                </span>
                                                <span className="text-xs text-muted-foreground">
                                                    {currency.code}
                                                </span>
                                            </span>
                                        </label>
                                    ))}
                                </div>
                                <p className="text-sm text-muted-foreground">
                                    Preview:{' '}
                                    {findCurrency(selectedCurrency).symbol}
                                    1,234.56
                                </p>
                                <InputError message={errors['settings.currency']} />
                            </div>

                            <Button
                                type="submit"
                                variant="brand"
                                disabled={processing}
                            >
                                Save workspace settings
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
