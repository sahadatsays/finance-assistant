import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Testimonial = {
    id: number;
    quote: string;
    author_name: string;
    author_role: string | null;
    sort_order: number;
    is_active: boolean;
};

export default function TestimonialsIndex({ testimonials }: { testimonials: Testimonial[] }) {
    return (
        <>
            <Head title="Testimonials" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Testimonials" description="Customer quotes displayed on the marketing homepage" />

                <div className="grid gap-4 md:grid-cols-2">
                    {testimonials.map((t) => (
                        <Card key={t.id} className="border-0 shadow-sm">
                            <CardHeader><CardTitle>{t.author_name}</CardTitle></CardHeader>
                            <CardContent className="text-sm text-muted-foreground">&ldquo;{t.quote}&rdquo;</CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Add Testimonial</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/testimonials" method="post" className="grid gap-4">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Quote</Label><Input name="quote" required /></div>
                                    <div className="grid gap-2"><Label>Author Name</Label><Input name="author_name" required /></div>
                                    <div className="grid gap-2"><Label>Author Role</Label><Input name="author_role" /></div>
                                    <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Add Testimonial</Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
