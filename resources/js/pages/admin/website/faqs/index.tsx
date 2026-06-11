import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Faq = { id: number; category: string; question: string; answer: string };

export default function FaqsIndex({ faqs }: { faqs: Faq[] }) {
    return (
        <>
            <Head title="FAQs" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="FAQs" description="Pricing and support frequently asked questions" />
                {faqs.map((faq) => (
                    <Card key={faq.id} className="border-0 shadow-sm">
                        <CardHeader><CardTitle className="text-base">{faq.question}</CardTitle></CardHeader>
                        <CardContent className="text-sm text-muted-foreground">{faq.answer}</CardContent>
                    </Card>
                ))}
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Add FAQ</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/faqs" method="post" className="grid gap-4">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Category</Label><Input name="category" defaultValue="pricing" /></div>
                                    <div className="grid gap-2"><Label>Question</Label><Input name="question" required /></div>
                                    <div className="grid gap-2"><Label>Answer</Label><Input name="answer" required /></div>
                                    <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Add FAQ</Button>
                                </>
                            )}
                        </Form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
