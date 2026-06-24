import { Link } from '@inertiajs/react';
import {
    ArrowLeftRight,
    LayoutGrid,
    PieChart,
    Tags,
    Target,
    Wallet,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { index as budgetsIndex } from '@/routes/budgets';
import { index as goalsIndex } from '@/routes/goals';
import { index as categoriesIndex } from '@/routes/categories';
import { index as accountsIndex } from '@/routes/accounts';
import { index as transactionsIndex } from '@/routes/transactions';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Categories',
        href: categoriesIndex(),
        icon: Tags,
    },
    {
        title: 'Accounts',
        href: accountsIndex(),
        icon: Wallet,
    },
    {
        title: 'Transactions',
        href: transactionsIndex(),
        icon: ArrowLeftRight,
    },
    {
        title: 'Budgets',
        href: budgetsIndex(),
        icon: PieChart,
    },
    {
        title: 'Savings Goals',
        href: goalsIndex(),
        icon: Target,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
