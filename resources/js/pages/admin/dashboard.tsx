import { Head } from '@inertiajs/react';
import {
    Building2,
    CircleDollarSign,
    FlaskConical,
    UserPlus,
    Users,
} from 'lucide-react';
import GrowthChart from '@/components/admin/charts/growth-chart';
import RegistrationChart from '@/components/admin/charts/registration-chart';
import RevenueChart from '@/components/admin/charts/revenue-chart';
import TenantStatisticsChart from '@/components/admin/charts/tenant-statistics-chart';
import StatCard from '@/components/admin/stat-card';

type Metrics = {
    total_tenants: number;
    active_tenants: number;
    trial_tenants: number;
    revenue: number;
    total_users: number;
    new_registrations: number;
};

type Charts = {
    growth: { month: string; tenants: number }[];
    registrations: { month: string; users: number }[];
    revenue: { month: string; revenue: number }[];
    tenant_statistics: { status: string; label: string; count: number }[];
};

export default function AdminDashboard({
    metrics,
    charts,
}: {
    metrics: Metrics;
    charts: Charts;
}) {
    return (
        <>
            <Head title="Super Admin Dashboard" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Dashboard</h1>
                    <p className="text-sm text-muted-foreground">
                        Platform overview and analytics
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                    <StatCard
                        title="Total Tenants"
                        value={metrics.total_tenants}
                        icon={Building2}
                        color="violet"
                    />
                    <StatCard
                        title="Active Tenants"
                        value={metrics.active_tenants}
                        icon={Building2}
                        color="green"
                    />
                    <StatCard
                        title="Trial Tenants"
                        value={metrics.trial_tenants}
                        icon={FlaskConical}
                        color="orange"
                    />
                    <StatCard
                        title="Revenue (MRR)"
                        value={`$${metrics.revenue.toFixed(2)}`}
                        icon={CircleDollarSign}
                        color="green"
                    />
                    <StatCard
                        title="Total Users"
                        value={metrics.total_users}
                        icon={Users}
                        color="blue"
                    />
                    <StatCard
                        title="New Registrations"
                        value={metrics.new_registrations}
                        subtitle="Last 30 days"
                        icon={UserPlus}
                        color="cyan"
                    />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <GrowthChart data={charts.growth} />
                    <RegistrationChart data={charts.registrations} />
                    <RevenueChart data={charts.revenue} />
                    <TenantStatisticsChart data={charts.tenant_statistics} />
                </div>
            </div>
        </>
    );
}
