import { Form, usePage } from '@inertiajs/react';
import TransactionController from '@/actions/App/Http/Controllers/Finance/TransactionController';
import TransactionFormFields, {
    type TransactionFormAccount,
    type TransactionFormCategory,
} from '@/components/transactions/transaction-form-fields';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Plus } from 'lucide-react';
import { useState } from 'react';

type QuickEntryProps = {
    accounts: TransactionFormAccount[];
    categories: TransactionFormCategory[];
};

type PageProps = {
    quickEntry: QuickEntryProps | null;
};

export default function QuickTransactionEntry() {
    const { quickEntry } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    if (!quickEntry) {
        return null;
    }

    return (
        <>
            <Button
                type="button"
                variant="brand"
                size="lg"
                className="fixed right-6 bottom-6 z-50 h-14 rounded-full px-5 shadow-lg"
                onClick={() => setOpen(true)}
            >
                <Plus className="size-5" />
                Quick Entry
            </Button>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Quick Transaction</DialogTitle>
                        <DialogDescription>
                            Add income, expense, or transfer in seconds.
                        </DialogDescription>
                    </DialogHeader>
                    <Form
                        {...TransactionController.store.form()}
                        encType="multipart/form-data"
                        className="grid gap-4 md:grid-cols-2"
                        onSuccess={() => setOpen(false)}
                    >
                        {({ processing, errors }) => (
                            <>
                                <TransactionFormFields
                                    accounts={quickEntry.accounts}
                                    categories={quickEntry.categories}
                                    compact
                                />
                                {errors.transaction && (
                                    <p className="text-sm text-destructive md:col-span-2">
                                        {errors.transaction}
                                    </p>
                                )}
                                <div className="md:col-span-2">
                                    <Button
                                        type="submit"
                                        variant="brand"
                                        disabled={processing}
                                        className="w-full"
                                    >
                                        Save Transaction
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
        </>
    );
}
