import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type NavItem = { id: number; location: string; label: string; route_name: string | null; url: string | null; sort_order: number; is_active: boolean };

export default function NavigationIndex({ items, locations }: { items: NavItem[]; locations: string[] }) {
    return (
        <>
            <Head title="Navigation Menu" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Navigation Menu" description="Header and primary navigation links" />
                {items.map((item) => (
                    <Card key={item.id} className="border-0 shadow-sm">
                        <CardHeader className="flex-row items-center justify-between">
                            <CardTitle className="text-base">{item.label}</CardTitle>
                            <Badge variant="secondary">{item.location}</Badge>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            {item.route_name ?? item.url} · order {item.sort_order}
                        </CardContent>
                    </Card>
                ))}
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Add Nav Item</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/navigation" method="post" className="grid gap-4 md:grid-cols-2">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label>Location</Label>
                                        <select name="location" className="h-9 rounded-md border px-3 text-sm">
                                            {locations.map((l) => <option key={l} value={l}>{l}</option>)}
                                        </select>
                                    </div>
                                    <div className="grid gap-2"><Label>Label</Label><Input name="label" required /></div>
                                    <div className="grid gap-2"><Label>Route Name</Label><Input name="route_name" placeholder="marketing.features" /></div>
                                    <div className="grid gap-2"><Label>URL (fallback)</Label><Input name="url" /></div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Add Item</Button>
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
