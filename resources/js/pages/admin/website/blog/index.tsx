import { Form, Head, Link, router } from '@inertiajs/react';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Post = {
    id: number;
    slug: string;
    title: string;
    category: string;
    status: string;
    excerpt: string | null;
    read_time_minutes: number;
    featured_image_url: string | null;
    author: { id: number; name: string } | null;
    published_at: string | null;
    updated_at: string | null;
    public_url: string;
};

type PaginatedPosts = {
    data: Post[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: { current_page: number; last_page: number; total: number };
};

export default function BlogIndex({
    posts,
    filters,
    stats,
    categories,
    statuses,
}: {
    posts: PaginatedPosts;
    filters: { search: string; status: string; category: string };
    stats: { total: number; published: number; draft: number };
    categories: string[];
    statuses: string[];
}) {
    const applyFilters = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const formData = new FormData(event.currentTarget);
        router.get('/admin/website/blog', Object.fromEntries(formData.entries()), { preserveState: true });
    };

    return (
        <>
            <Head title="Blog Posts" />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="Blog Posts"
                    description="Create, publish, and manage marketing articles"
                    action={
                        <Button asChild className="bg-violet-600 hover:bg-violet-700">
                            <Link href="/admin/website/blog/create">New Post</Link>
                        </Button>
                    }
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Total</CardTitle></CardHeader>
                        <CardContent className="text-2xl font-semibold">{stats.total}</CardContent>
                    </Card>
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Published</CardTitle></CardHeader>
                        <CardContent className="text-2xl font-semibold text-emerald-600">{stats.published}</CardContent>
                    </Card>
                    <Card className="border-0 shadow-sm">
                        <CardHeader className="pb-2"><CardTitle className="text-sm font-medium text-muted-foreground">Drafts</CardTitle></CardHeader>
                        <CardContent className="text-2xl font-semibold text-amber-600">{stats.draft}</CardContent>
                    </Card>
                </div>

                <Card className="border-0 shadow-sm">
                    <CardHeader><CardTitle>Filters</CardTitle></CardHeader>
                    <CardContent>
                        <form onSubmit={applyFilters} className="grid gap-4 md:grid-cols-4">
                            <div className="grid gap-2">
                                <Label>Search</Label>
                                <Input name="search" defaultValue={filters.search} placeholder="Title, slug, excerpt..." />
                            </div>
                            <div className="grid gap-2">
                                <Label>Status</Label>
                                <select
                                    name="status"
                                    defaultValue={filters.status}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                >
                                    <option value="">All statuses</option>
                                    {statuses.map((status) => (
                                        <option key={status} value={status}>{status}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="grid gap-2">
                                <Label>Category</Label>
                                <select
                                    name="category"
                                    defaultValue={filters.category}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                >
                                    <option value="">All categories</option>
                                    {categories.map((category) => (
                                        <option key={category} value={category}>{category}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex items-end">
                                <Button type="submit" variant="outline" className="w-full">Apply</Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <div className="space-y-3">
                    {posts.data.length === 0 && (
                        <Card className="border-0 shadow-sm">
                            <CardContent className="py-10 text-center text-sm text-muted-foreground">
                                No blog posts match your filters. Create your first article to get started.
                            </CardContent>
                        </Card>
                    )}

                    {posts.data.map((post) => (
                        <Card key={post.id} className="border-0 shadow-sm">
                            <CardContent className="flex flex-col gap-4 p-5 md:flex-row md:items-center md:justify-between">
                                <div className="flex gap-4">
                                    {post.featured_image_url && (
                                        <img
                                            src={post.featured_image_url}
                                            alt=""
                                            className="hidden h-16 w-24 rounded-md border object-cover sm:block"
                                        />
                                    )}
                                    <div>
                                        <div className="flex flex-wrap items-center gap-2">
                                            <h3 className="font-semibold">{post.title}</h3>
                                            <Badge variant={post.status === 'published' ? 'default' : 'secondary'}>{post.status}</Badge>
                                        </div>
                                        <p className="mt-1 text-sm text-muted-foreground line-clamp-2">{post.excerpt}</p>
                                        <p className="mt-2 text-xs text-muted-foreground">
                                            {post.category} · {post.read_time_minutes} min
                                            {post.author ? ` · ${post.author.name}` : ''}
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {post.status === 'published' && (
                                        <Button asChild size="sm" variant="outline">
                                            <a href={post.public_url} target="_blank" rel="noreferrer">Preview</a>
                                        </Button>
                                    )}
                                    <Button asChild size="sm" variant="outline">
                                        <Link href={`/admin/website/blog/${post.id}/edit`}>Edit</Link>
                                    </Button>
                                    {post.status === 'published' ? (
                                        <Form action={`/admin/website/blog/${post.id}/unpublish`} method="post">
                                            {({ processing }) => (
                                                <Button type="submit" size="sm" variant="outline" disabled={processing}>Unpublish</Button>
                                            )}
                                        </Form>
                                    ) : (
                                        <Form action={`/admin/website/blog/${post.id}/publish`} method="post">
                                            {({ processing }) => (
                                                <Button type="submit" size="sm" className="bg-violet-600 hover:bg-violet-700" disabled={processing}>Publish</Button>
                                            )}
                                        </Form>
                                    )}
                                    <Form action={`/admin/website/blog/${post.id}`} method="delete">
                                        {({ processing }) => (
                                            <Button type="submit" size="sm" variant="destructive" disabled={processing}>Delete</Button>
                                        )}
                                    </Form>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {posts.meta.last_page > 1 && (
                    <div className="flex flex-wrap gap-2">
                        {posts.links.map((link) => (
                            link.url ? (
                                <Button
                                    key={link.label}
                                    asChild
                                    size="sm"
                                    variant={link.active ? 'default' : 'outline'}
                                >
                                    <Link href={link.url} dangerouslySetInnerHTML={{ __html: link.label }} />
                                </Button>
                            ) : (
                                <Button key={link.label} size="sm" variant="outline" disabled dangerouslySetInnerHTML={{ __html: link.label }} />
                            )
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
