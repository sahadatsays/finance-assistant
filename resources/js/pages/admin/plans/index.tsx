import { Form, Head } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Admin/PlanController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
};

export default function AdminPlansIndex({ plans }: { plans: Plan[] }) {
    return (
        <>
            <Head title="Subscription Plans" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Subscription Plans
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Manage pricing tiers (no payment gateway yet)
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    {plans.map((plan) => (
                        <Card key={plan.id} className="border-0 shadow-sm">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle>{plan.name}</CardTitle>
                                    <Badge variant="secondary">
                                        ${plan.price_monthly}/mo
                                    </Badge>
                                </div>
                                <CardDescription>
                                    {plan.description}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <p>Max users: {plan.max_users}</p>
                                <p className="text-muted-foreground">
                                    Slug: {plan.slug}
                                </p>
                                {plan.features && (
                                    <ul className="list-inside list-disc text-muted-foreground">
                                        {plan.features.map((f) => (
                                            <li key={f}>{f}</li>
                                        ))}
                                    </ul>
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader>
                        <CardTitle>Create Plan</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Form
                            {...PlanController.store.form()}
                            className="grid gap-4 md:grid-cols-2"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label>Name</Label>
                                        <Input name="name" required />
                                        {errors.name && (
                                            <p className="text-sm text-destructive">
                                                {errors.name}
                                            </p>
                                        )}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Slug</Label>
                                        <Input name="slug" required />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Price (monthly)</Label>
                                        <Input
                                            name="price_monthly"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Max Users</Label>
                                        <Input
                                            name="max_users"
                                            type="number"
                                            min="1"
                                            required
                                        />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label>Description</Label>
                                        <Input name="description" />
                                    </div>
                                    <div className="md:col-span-2">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="bg-violet-600 hover:bg-violet-700"
                                        >
                                            Create Plan
                                        </Button>
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
