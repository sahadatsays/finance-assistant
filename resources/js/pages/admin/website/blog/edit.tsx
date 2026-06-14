import { Form, Head, Link } from '@inertiajs/react';
import BlogPostForm from '@/components/admin/blog-post-form';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type MediaAsset = { id: number; filename: string; url: string; alt_text: string | null };

type Post = {
    id: number;
    slug: string;
    title: string;
    excerpt: string | null;
    body: string | null;
    category: string;
    status: string;
    meta_title: string | null;
    meta_description: string | null;
    featured_image_id: number | null;
    public_url: string;
    published_at: string | null;
    updated_at: string | null;
};

export default function BlogEdit({
    post,
    categories,
    statuses,
    mediaAssets,
}: {
    post: Post;
    categories: string[];
    statuses: string[];
    mediaAssets: MediaAsset[];
}) {
    return (
        <>
            <Head title={`Edit: ${post.title}`} />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="Edit Blog Post"
                    description={post.slug}
                    action={
                        <div className="flex flex-wrap items-center gap-2">
                            <Badge variant={post.status === 'published' ? 'default' : 'secondary'}>{post.status}</Badge>
                            {post.status === 'published' && (
                                <Button asChild variant="outline" size="sm">
                                    <a href={post.public_url} target="_blank" rel="noreferrer">View live</a>
                                </Button>
                            )}
                            <Button asChild variant="outline" size="sm">
                                <Link href="/admin/website/blog">Back to list</Link>
                            </Button>
                        </div>
                    }
                />
                <BlogPostForm
                    action={`/admin/website/blog/${post.id}`}
                    method="patch"
                    submitLabel="Save Changes"
                    categories={categories}
                    statuses={statuses}
                    mediaAssets={mediaAssets}
                    initial={{
                        title: post.title,
                        slug: post.slug,
                        excerpt: post.excerpt ?? '',
                        body: post.body ?? '',
                        category: post.category,
                        status: post.status,
                        meta_title: post.meta_title ?? '',
                        meta_description: post.meta_description ?? '',
                        featured_image_id: post.featured_image_id,
                    }}
                />
                <div className="flex flex-wrap gap-2 border-t pt-4">
                    {post.status === 'published' ? (
                        <Form action={`/admin/website/blog/${post.id}/unpublish`} method="post">
                            {({ processing }) => (
                                <Button type="submit" variant="outline" disabled={processing}>Unpublish</Button>
                            )}
                        </Form>
                    ) : (
                        <Form action={`/admin/website/blog/${post.id}/publish`} method="post">
                            {({ processing }) => (
                                <Button type="submit" className="bg-violet-600 hover:bg-violet-700" disabled={processing}>Publish now</Button>
                            )}
                        </Form>
                    )}
                    <Form action={`/admin/website/blog/${post.id}`} method="delete">
                        {({ processing }) => (
                            <Button type="submit" variant="destructive" disabled={processing}>Delete post</Button>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
