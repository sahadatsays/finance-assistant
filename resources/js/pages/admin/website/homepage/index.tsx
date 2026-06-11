import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Stat = { label: string; value: string };
type Feature = { title: string; description: string };
type Cta = { title: string; subtitle?: string; primary_label?: string; primary_url?: string };

type Homepage = {
    hero_eyebrow: string | null;
    hero_title: string | null;
    hero_subtitle: string | null;
    hero_primary_label: string | null;
    hero_primary_url: string | null;
    hero_secondary_label: string | null;
    hero_secondary_url: string | null;
    hero_image_id: number | null;
    hero_image_url: string | null;
    statistics: Stat[];
    features: Feature[];
    cta_sections: Cta[];
    is_active: boolean;
};

export default function HomepageIndex({ homepage }: { homepage: Homepage }) {
    return (
        <>
            <Head title="Homepage Builder" />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="Homepage Builder"
                    description="Hero section, statistics, features, and CTA blocks"
                />

                <Form action="/admin/website/homepage" method="patch" className="space-y-6">
                    {({ processing }) => (
                        <>
                            <Card className="border-0 shadow-sm">
                                <CardHeader>
                                    <CardTitle>Hero Section</CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-4 md:grid-cols-2">
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label>Eyebrow</Label>
                                        <Input name="hero_eyebrow" defaultValue={homepage.hero_eyebrow ?? ''} />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label>Title</Label>
                                        <Input name="hero_title" defaultValue={homepage.hero_title ?? ''} />
                                    </div>
                                    <div className="grid gap-2 md:col-span-2">
                                        <Label>Subtitle</Label>
                                        <textarea name="hero_subtitle" defaultValue={homepage.hero_subtitle ?? ''} rows={3} className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs" />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Primary Button Label</Label>
                                        <Input name="hero_primary_label" defaultValue={homepage.hero_primary_label ?? ''} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Primary Button URL</Label>
                                        <Input name="hero_primary_url" defaultValue={homepage.hero_primary_url ?? ''} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Secondary Button Label</Label>
                                        <Input name="hero_secondary_label" defaultValue={homepage.hero_secondary_label ?? ''} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Secondary Button URL</Label>
                                        <Input name="hero_secondary_url" defaultValue={homepage.hero_secondary_url ?? ''} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Hero Image ID</Label>
                                        <Input name="hero_image_id" type="number" defaultValue={homepage.hero_image_id ?? ''} />
                                        {homepage.hero_image_url && (
                                            <img src={homepage.hero_image_url} alt="" className="mt-2 h-24 rounded border object-cover" />
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card className="border-0 shadow-sm">
                                <CardHeader><CardTitle>Statistics (JSON)</CardTitle></CardHeader>
                                <CardContent>
                                    <textarea
                                        name="statistics"
                                        rows={6}
                                        defaultValue={JSON.stringify(homepage.statistics ?? [], null, 2)}
                                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs"
                                    />
                                    <p className="mt-2 text-xs text-muted-foreground">Format: [{'{'}&quot;label&quot;:&quot;...&quot;,&quot;value&quot;:&quot;...&quot;{'}'}]</p>
                                </CardContent>
                            </Card>

                            <Card className="border-0 shadow-sm">
                                <CardHeader><CardTitle>Features (JSON)</CardTitle></CardHeader>
                                <CardContent>
                                    <textarea
                                        name="features"
                                        rows={8}
                                        defaultValue={JSON.stringify(homepage.features ?? [], null, 2)}
                                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs"
                                    />
                                </CardContent>
                            </Card>

                            <Card className="border-0 shadow-sm">
                                <CardHeader><CardTitle>CTA Sections (JSON)</CardTitle></CardHeader>
                                <CardContent>
                                    <textarea
                                        name="cta_sections"
                                        rows={6}
                                        defaultValue={JSON.stringify(homepage.cta_sections ?? [], null, 2)}
                                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs"
                                    />
                                </CardContent>
                            </Card>

                            <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">
                                Save Homepage
                            </Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
