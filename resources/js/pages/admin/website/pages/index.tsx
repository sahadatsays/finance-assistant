import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Page = { id: number; slug: string; title: string; status: string };

export default function WebsitePagesIndex({ pages }: { pages: Page[] }) {
    return (
        <>
            <Head title="Website Pages" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Website Pages" description="Static marketing pages (privacy, terms, about, etc.)" />
                {pages.map((page) => (
                    <Card key={page.id} className="border-0 shadow-sm">
                        <CardHeader className="flex-row items-center justify-between">
                            <CardTitle>{page.title}</CardTitle>
                            <Badge>{page.status}</Badge>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">/{page.slug}</CardContent>
                    </Card>
                ))}
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Create Page</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/pages" method="post" className="grid gap-4 md:grid-cols-2">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Title</Label><Input name="title" required /></div>
                                    <div className="grid gap-2"><Label>Slug</Label><Input name="slug" required /></div>
                                    <div className="grid gap-2"><Label>Status</Label><Input name="status" defaultValue="draft" /></div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Create Page</Button>
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
