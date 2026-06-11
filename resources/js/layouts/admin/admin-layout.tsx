import type { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import AdminSidebar from '@/layouts/admin/admin-sidebar';
import type { Auth } from '@/types';

type PageProps = {
    auth: Auth & { isPlatformAdmin?: boolean };
};

export default function AdminLayout({ children }: PropsWithChildren) {
    const { auth } = usePage<PageProps>().props;

    return (
        <div className="flex min-h-screen bg-[#f8f7fa] dark:bg-zinc-950">
            <AdminSidebar />
            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex h-16 items-center justify-between border-b bg-white px-6 dark:border-zinc-800 dark:bg-zinc-900">
                    <div>
                        <p className="text-xs text-muted-foreground">
                            Platform Administration
                        </p>
                        <p className="font-medium">{auth.user.name}</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <Link
                            href="/dashboard"
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            User App
                        </Link>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                        >
                            <LogOut className="size-4" />
                            Logout
                        </Link>
                    </div>
                </header>
                <main className="flex-1 overflow-auto p-6">{children}</main>
            </div>
        </div>
    );
}
