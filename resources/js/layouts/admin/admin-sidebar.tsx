import { Link } from '@inertiajs/react';
import {
    Activity,
    Building2,
    CreditCard,
    Globe,
    LayoutDashboard,
    Settings,
} from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';

const navItems = [
    { title: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { title: 'Tenants', href: '/admin/tenants', icon: Building2 },
    { title: 'Plans', href: '/admin/plans', icon: CreditCard },
    { title: 'Website', href: '/admin/website', icon: Globe },
    { title: 'Settings', href: '/admin/settings', icon: Settings },
    { title: 'Activity Logs', href: '/admin/activity-logs', icon: Activity },
];

export default function AdminSidebar() {
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <aside className="flex w-64 shrink-0 flex-col bg-[#2f3349] text-white">
            <div className="border-b border-white/10 px-6 py-5">
                <Link href="/admin" className="flex items-center gap-2">
                    <div className="flex size-8 items-center justify-center rounded-lg bg-violet-500 font-bold">
                        FA
                    </div>
                    <div>
                        <p className="text-sm font-semibold">Finance Assistant</p>
                        <p className="text-xs text-white/60">Super Admin</p>
                    </div>
                </Link>
            </div>

            <nav className="flex-1 space-y-1 px-3 py-4">
                {navItems.map((item) => (
                    <Link
                        key={item.href}
                        href={item.href}
                        className={cn(
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors',
                            isCurrentOrParentUrl(item.href)
                                ? 'bg-violet-500/20 text-violet-200'
                                : 'text-white/70 hover:bg-white/5 hover:text-white',
                        )}
                    >
                        <item.icon className="size-4" />
                        {item.title}
                    </Link>
                ))}
            </nav>

            <div className="border-t border-white/10 px-6 py-4 text-xs text-white/50">
                Vuexy Admin Panel
            </div>
        </aside>
    );
}
