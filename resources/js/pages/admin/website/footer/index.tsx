import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type FooterSettings = {
    copyright_text: string | null;
    tagline: string | null;
    show_newsletter: boolean;
    newsletter_heading: string | null;
};

type FooterLink = { id: number; location: string; label: string; route_name: string | null; url: string | null };

export default function FooterIndex({
    settings,
    links,
    locations,
}: {
    settings: FooterSettings;
    links: FooterLink[];
    locations: string[];
}) {
    return (
        <>
            <Head title="Footer Management" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Footer Management" description="Footer settings and link columns" />
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Footer Settings</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/footer/settings" method="patch" className="grid gap-4">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Copyright Text</Label><Input name="copyright_text" defaultValue={settings.copyright_text ?? ''} /></div>
                                    <div className="grid gap-2"><Label>Tagline</Label><Input name="tagline" defaultValue={settings.tagline ?? ''} /></div>
                                    <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Save Settings</Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
                {links.map((link) => (
                    <Card key={link.id} className="border-0 shadow-sm">
                        <CardContent className="pt-6 text-sm">{link.location}: {link.label}</CardContent>
                    </Card>
                ))}
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Add Footer Link</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/footer/links" method="post" className="grid gap-4 md:grid-cols-2">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label>Column</Label>
                                        <select name="location" className="h-9 rounded-md border px-3 text-sm">
                                            {locations.map((l) => <option key={l} value={l}>{l}</option>)}
                                        </select>
                                    </div>
                                    <div className="grid gap-2"><Label>Label</Label><Input name="label" required /></div>
                                    <div className="grid gap-2"><Label>Route Name</Label><Input name="route_name" /></div>
                                    <div className="grid gap-2"><Label>URL</Label><Input name="url" /></div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Add Link</Button>
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
