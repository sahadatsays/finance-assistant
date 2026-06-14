import { Head, Link } from '@inertiajs/react';
import BlogPostForm from '@/components/admin/blog-post-form';
import WebsiteModuleHeader from '@/components/admin/website-module-header';
import { Button } from '@/components/ui/button';

type MediaAsset = { id: number; filename: string; url: string; alt_text: string | null };

export default function BlogCreate({
    categories,
    statuses,
    mediaAssets,
}: {
    categories: string[];
    statuses: string[];
    mediaAssets: MediaAsset[];
}) {
    return (
        <>
            <Head title="Create Blog Post" />
            <div className="space-y-6">
                <WebsiteModuleHeader
                    title="Create Blog Post"
                    description="Draft a new article for the marketing blog"
                    action={
                        <Button asChild variant="outline">
                            <Link href="/admin/website/blog">Back to list</Link>
                        </Button>
                    }
                />
                <BlogPostForm
                    action="/admin/website/blog"
                    method="post"
                    submitLabel="Create Post"
                    categories={categories}
                    statuses={statuses}
                    mediaAssets={mediaAssets}
                />
            </div>
        </>
    );
}
