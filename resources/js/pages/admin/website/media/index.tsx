import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Asset = { id: number; filename: string; mime_type: string; size: number; url: string; alt_text: string | null };
type Paginated = { data: Asset[] };

export default function MediaIndex({ assets }: { assets: Paginated }) {
    return (
        <>
            <Head title="Media Library" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Media Library" description="Upload images for homepage, SEO, and blog posts" />
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Upload</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/media" method="post" encType="multipart/form-data" className="grid gap-4 max-w-md">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>File</Label><Input name="file" type="file" accept="image/*" required /></div>
                                    <div className="grid gap-2"><Label>Alt Text</Label><Input name="alt_text" /></div>
                                    <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Upload</Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {assets.data.map((asset) => (
                        <Card key={asset.id} className="border-0 shadow-sm overflow-hidden">
                            <img src={asset.url} alt={asset.alt_text ?? asset.filename} className="h-32 w-full object-cover" />
                            <CardContent className="p-3 text-xs text-muted-foreground">
                                <p className="truncate font-medium text-foreground">{asset.filename}</p>
                                <p>ID: {asset.id}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}
