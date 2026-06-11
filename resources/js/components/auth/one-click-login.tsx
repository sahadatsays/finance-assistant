import { Link } from '@inertiajs/react';
import { Zap } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { login as devLogin } from '@/routes/dev';

export type OneClickAccount = {
    id: number;
    name: string;
    email: string;
    label: string;
    description: string;
};

type Props = {
    accounts: OneClickAccount[];
};

export default function OneClickLogin({ accounts }: Props) {
    if (accounts.length === 0) {
        return null;
    }

    return (
        <div
            className="rounded-xl border border-dashed border-violet-300 bg-violet-50/50 p-4 dark:border-violet-800 dark:bg-violet-950/20"
            data-test="one-click-login"
        >
            <div className="mb-3 flex items-center gap-2">
                <Zap className="size-4 text-violet-600 dark:text-violet-400" />
                <p className="text-sm font-medium text-violet-900 dark:text-violet-100">
                    One-Click Login
                </p>
                <Badge
                    variant="secondary"
                    className="bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-200"
                >
                    Dev
                </Badge>
            </div>

            <div className="grid gap-2">
                {accounts.map((account) => (
                    <Link
                        key={account.id}
                        href={devLogin.url(account.id)}
                        method="post"
                        as="button"
                        className="flex w-full items-center justify-between rounded-lg border bg-white px-3 py-2 text-left transition-colors hover:border-violet-400 hover:bg-violet-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-violet-600 dark:hover:bg-violet-950/40"
                        data-test={`one-click-login-${account.email}`}
                    >
                        <div className="min-w-0">
                            <p className="truncate text-sm font-medium">
                                {account.label}
                            </p>
                            <p className="truncate text-xs text-muted-foreground">
                                {account.description}
                            </p>
                        </div>
                        <span className="ml-2 shrink-0 text-xs text-muted-foreground">
                            {account.email}
                        </span>
                    </Link>
                ))}
            </div>
        </div>
    );
}
