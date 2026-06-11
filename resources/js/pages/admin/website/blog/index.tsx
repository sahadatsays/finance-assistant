import { Form, Head } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Post = { id: number; slug: string; title: string; category: string; status: string; excerpt: string | null };

export default function BlogIndex({ posts }: { posts: Post[] }) {
    return (
        <>
            <Head title="Blog Posts" />
            <div className="space-y-6">
                <WebsiteModuleHeader title="Blog Posts" description="Publish articles and product updates" />
                <div className="space-y-3">
                    {posts.map((post) => (
                        <Card key={post.id} className="border-0 shadow-sm">
                            <CardHeader className="flex-row items-center justify-between">
                                <CardTitle className="text-base">{post.title}</CardTitle>
                                <Badge variant="secondary">{post.status}</Badge>
                            </CardHeader>
                            <CardContent className="text-sm text-muted-foreground">{post.excerpt}</CardContent>
                        </Card>
                    ))}
                </div>
                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Create Post</CardTitle></CardHeader>
                    <CardContent>
                        <Form action="/admin/website/blog" method="post" className="grid gap-4 md:grid-cols-2">
                            {({ processing }) => (
                                <>
                                    <div className="grid gap-2"><Label>Title</Label><Input name="title" required /></div>
                                    <div className="grid gap-2"><Label>Slug</Label><Input name="slug" required /></div>
                                    <div className="grid gap-2"><Label>Category</Label><Input name="category" defaultValue="Guides" /></div>
                                    <div className="grid gap-2"><Label>Status</Label><Input name="status" defaultValue="draft" /></div>
                                    <div className="grid gap-2 md:col-span-2"><Label>Excerpt</Label><Input name="excerpt" /></div>
                                    <div className="md:col-span-2">
                                        <Button type="submit" disabled={processing} className="bg-violet-600 hover:bg-violet-700">Create Post</Button>
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
