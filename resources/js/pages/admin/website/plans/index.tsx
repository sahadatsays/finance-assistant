import { Form, Head, router } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Admin/PlanController';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Plan = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    price_monthly: string;
    max_users: number;
    features: string[] | null;
    sort_order?: number;
};

export default function WebsitePlansIndex({
    plans,
    featureLabels,
}: {
    plans: Plan[];
    featureLabels: Record<string, string>;
}) {
    const movePlan = (plan: Plan, direction: 'up' | 'down') => {
        const sorted = [...plans].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
        const index = sorted.findIndex((p) => p.id === plan.id);
        const swapIndex = direction === 'up' ? index - 1 : index + 1;
        if (swapIndex < 0 || swapIndex >= sorted.length) return;

        const reordered = sorted.map((p, i) => {
            if (i === index) return { id: p.id, sort_order: swapIndex };
            if (i === swapIndex) return { id: p.id, sort_order: index };
            return { id: p.id, sort_order: i };
        });

        router.post('/admin/website/plans/reorder', { plans: reordered });
    };

    return (
        <>
            <Head title="Pricing Plans" />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="Pricing Plans"
                    description="Create, update, reorder plans and manage feature lists"
                />

                <div className="grid gap-4 md:grid-cols-3">
                    {[...plans].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0)).map((plan) => (
                        <Card key={plan.id} className="border-0 shadow-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>{plan.name}</CardTitle>
                                    <Badge>${plan.price_monthly}/mo</Badge>
                                </div>
                                <CardDescription>{plan.description}</CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <p>Max users: {plan.max_users}</p>
                                {plan.features?.map((f) => (
                                    <p key={f} className="text-muted-foreground">• {featureLabels[f] ?? f}</p>
                                ))}
                                <div className="flex gap-2 pt-2">
                                    <Button type="button" size="sm" variant="outline" onClick={() => movePlan(plan, 'up')}>↑</Button>
                                    <Button type="button" size="sm" variant="outline" onClick={() => movePlan(plan, 'down')}>↓</Button>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Create Plan</CardTitle></CardHeader>
                    <CardContent>
                        <Form {...PlanController.store.form()} className="grid gap-4 md:grid-cols-2">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Name</Label><Input name="name" required /></div>
                                    <div className="grid gap-2"><Label>Slug</Label><Input name="slug" required /></div>
                                    <div className="grid gap-2"><Label>Price</Label><Input name="price_monthly" type="number" step="0.01" required /></div>
                                    <div className="grid gap-2"><Label>Max Users</Label><Input name="max_users" type="number" required /></div>
                                    <div className="grid gap-2 md:col-span-2"><Label>Description</Label><Input name="description" /></div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Create Plan</Button>
                                    </div>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
