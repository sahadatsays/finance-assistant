import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type SeoEntry = {
    id: number;
    page_key: string;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string[] | null;
    og_image_id: number | null;
    canonical_url: string | null;
};

export default function SeoIndex({ entries }: { entries: SeoEntry[] }) {
    return (
        <>
            <Head title="SEO Management" />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="SEO Management"
                    description="Meta titles, descriptions, keywords, Open Graph images, and canonical URLs"
                />
                {entries.map((entry) => (
                    <Card key={entry.id} className="border-0 shadow-sm">
                        <CardHeader>
                            <CardTitle className="capitalize">{entry.page_key.replace('_', ' ')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Form action={`/admin/website/seo/${entry.id}`} method="patch" className="grid gap-4 md:grid-cols-2">
                                {({ processing }) => (
                                    <>
                                        <div className="grid gap-2 md:col-span-2">
                                            <Label>Meta Title</Label>
                                            <Input name="meta_title" defaultValue={entry.meta_title ?? ''} />
                                        </div>
                                        <div className="grid gap-2 md:col-span-2">
                                            <Label>Meta Description</Label>
                                            <Input name="meta_description" defaultValue={entry.meta_description ?? ''} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label>Keywords (comma-separated)</Label>
                                            <Input name="meta_keywords" defaultValue={(entry.meta_keywords ?? []).join(', ')} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label>OG Image ID</Label>
                                            <Input name="og_image_id" type="number" defaultValue={entry.og_image_id ?? ''} />
                                        </div>
                                        <div className="grid gap-2 md:col-span-2">
                                            <Label>Canonical URL</Label>
                                            <Input name="canonical_url" defaultValue={entry.canonical_url ?? ''} />
                                        </div>
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700 md:col-span-2">
                                            Save SEO
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </>
    );
}
