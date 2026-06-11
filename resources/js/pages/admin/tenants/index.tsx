import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';
import TenantPageController from '@/actions/App/Http/Controllers/Admin/TenantPageController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Tenant = {
    id: number;
    name: string;
    slug: string;
    status: string;
    users_count?: number;
    subscription?: { plan?: { name: string } };
};

type Plan = { id: number; name: string; slug: string };

export default function AdminTenantsIndex({
    tenants,
    meta,
    filters,
    plans,
}: {
    tenants: { data: Tenant[] };
    meta: { total: number };
    filters: { status?: string; search?: string };
    plans: Plan[];
}) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <Head title="Tenant Management" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Tenant Management
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {meta.total} tenants total
                        </p>
                    </div>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button className="bg-violet-600 hover:bg-violet-700">
                                Create Tenant
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Create Tenant</DialogTitle>
                            </DialogHeader>
                            <Form
                                {...TenantPageController.store.form()}
                                onSuccess={() => setOpen(false)}
                                className="space-y-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="name">Name</Label>
                                            <Input
                                                id="name"
                                                name="name"
                                                required
                                            />
                                            {errors.name && (
                                                <p className="text-sm text-destructive">
                                                    {errors.name}
                                                </p>
                                            )}
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="owner_email">
                                                Owner Email
                                            </Label>
                                            <Input
                                                id="owner_email"
                                                name="owner_email"
                                                type="email"
                                                required
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="plan_id">Plan</Label>
                                            <Select name="plan_id">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select plan" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {plans.map((plan) => (
                                                        <SelectItem
                                                            key={plan.id}
                                                            value={String(
                                                                plan.id,
                                                            )}
                                                        >
                                                            {plan.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="w-full bg-violet-600 hover:bg-violet-700"
                                        >
                                            Create
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle>All Tenants</CardTitle>
                        <CardDescription>
                            Suspend, activate, and manage workspaces
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 flex gap-2">
                            <Input
                                placeholder="Search tenants..."
                                defaultValue={filters.search}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        router.get('/admin/tenants', {
                                            search: e.currentTarget.value,
                                        });
                                    }
                                }}
                            />
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead>
                                    <tr className="border-b text-left text-muted-foreground">
                                        <th className="pb-3 pr-4">Name</th>
                                        <th className="pb-3 pr-4">Status</th>
                                        <th className="pb-3 pr-4">Plan</th>
                                        <th className="pb-3 pr-4">Users</th>
                                        <th className="pb-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tenants.data.map((tenant) => (
                                        <tr
                                            key={tenant.id}
                                            className="border-b last:border-0"
                                        >
                                            <td className="py-3 pr-4 font-medium">
                                                {tenant.name}
                                                <p className="text-xs text-muted-foreground">
                                                    {tenant.slug}
                                                </p>
                                            </td>
                                            <td className="py-3 pr-4">
                                                <Badge variant="secondary">
                                                    {tenant.status}
                                                </Badge>
                                            </td>
                                            <td className="py-3 pr-4">
                                                {tenant.subscription?.plan
                                                    ?.name ?? '—'}
                                            </td>
                                            <td className="py-3 pr-4">
                                                {tenant.users_count ?? 0}
                                            </td>
                                            <td className="py-3">
                                                <div className="flex gap-2">
                                                    {tenant.status !==
                                                        'suspended' && (
                                                        <Form
                                                            {...TenantPageController.suspend.form(
                                                                {
                                                                    tenant: tenant.id,
                                                                },
                                                            )}
                                                        >
                                                            <Button
                                                                type="submit"
                                                                size="sm"
                                                                variant="outline"
                                                            >
                                                                Suspend
                                                            </Button>
                                                        </Form>
                                                    )}
                                                    {tenant.status ===
                                                        'suspended' && (
                                                        <Form
                                                            {...TenantPageController.activate.form(
                                                                {
                                                                    tenant: tenant.id,
                                                                },
                                                            )}
                                                        >
                                                            <Button
                                                                type="submit"
                                                                size="sm"
                                                                className="bg-violet-600 hover:bg-violet-700"
                                                            >
                                                                Activate
                                                            </Button>
                                                        </Form>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
