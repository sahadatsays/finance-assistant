import { Form } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type MediaAsset = { id: number; filename: string; url: string; alt_text: string | null };

type BlogPostValues = {
    title: string;
    slug: string;
    excerpt: string;
    body: string;
    category: string;
    status: string;
    meta_title: string;
    meta_description: string;
    featured_image_id: number | null;
};

type BlogPostFormProps = {
    action: string;
    method: 'post' | 'patch';
    submitLabel: string;
    categories: string[];
    statuses: string[];
    mediaAssets: MediaAsset[];
    initial?: Partial<BlogPostValues>;
};

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

export default function BlogPostForm({
    action,
    method,
    submitLabel,
    categories,
    statuses,
    mediaAssets,
    initial = {},
}: BlogPostFormProps) {
    const defaults = useMemo(
        () => ({
            title: initial.title ?? '',
            slug: initial.slug ?? '',
            excerpt: initial.excerpt ?? '',
            body: initial.body ?? '',
            category: initial.category ?? categories[0] ?? 'Guides',
            status: initial.status ?? 'draft',
            meta_title: initial.meta_title ?? '',
            meta_description: initial.meta_description ?? '',
            featured_image_id: initial.featured_image_id ?? null,
        }),
        [categories, initial],
    );

    const [slug, setSlug] = useState(defaults.slug);
    const [slugTouched, setSlugTouched] = useState(Boolean(initial.slug));

    const selectedImage = mediaAssets.find((asset) => asset.id === defaults.featured_image_id);

    return (
        <Form action={action} method={method} className="grid gap-6 lg:grid-cols-[2fr_1fr]">
            {({ processing }) => (
                <>
                    <div className="space-y-6">
                        <Card className="border-0 shadow-sm">
                            <CardHeader>
                                <CardTitle>Content</CardTitle>
                                <CardDescription>Write your article in Markdown. Headings, lists, and links are supported.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="title">Title</Label>
                                    <Input
                                        id="title"
                                        name="title"
                                        defaultValue={defaults.title}
                                        required
                                        onChange={(event) => {
                                            if (!slugTouched) {
                                                setSlug(slugify(event.target.value));
                                            }
                                        }}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        name="slug"
                                        value={slug}
                                        onChange={(event) => {
                                            setSlugTouched(true);
                                            setSlug(event.target.value);
                                        }}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="excerpt">Excerpt</Label>
                                    <textarea
                                        id="excerpt"
                                        name="excerpt"
                                        defaultValue={defaults.excerpt}
                                        rows={3}
                                        className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="body">Body (Markdown)</Label>
                                    <textarea
                                        id="body"
                                        name="body"
                                        defaultValue={defaults.body}
                                        rows={18}
                                        className="min-h-[360px] w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-sm shadow-xs"
                                        placeholder="## Introduction&#10;&#10;Write your article here..."
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-sm">
                            <CardHeader>
                                <CardTitle>SEO</CardTitle>
                                <CardDescription>Override default title and description for search engines.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="meta_title">Meta title</Label>
                                    <Input id="meta_title" name="meta_title" defaultValue={defaults.meta_title} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="meta_description">Meta description</Label>
                                    <textarea
                                        id="meta_description"
                                        name="meta_description"
                                        defaultValue={defaults.meta_description}
                                        rows={3}
                                        className="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs"
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card className="border-0 shadow-sm">
                            <CardHeader>
                                <CardTitle>Publish</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <select
                                        id="status"
                                        name="status"
                                        defaultValue={defaults.status}
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                    >
                                        {statuses.map((status) => (
                                            <option key={status} value={status}>
                                                {status}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="category">Category</Label>
                                    <select
                                        id="category"
                                        name="category"
                                        defaultValue={defaults.category}
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                    >
                                        {categories.map((category) => (
                                            <option key={category} value={category}>
                                                {category}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <Button type="submit" disabled={processing} className="w-full bg-violet-600 hover:bg-violet-700">
                                    {submitLabel}
                                </Button>
                            </CardContent>
                        </Card>

                        <Card className="border-0 shadow-sm">
                            <CardHeader>
                                <CardTitle>Featured image</CardTitle>
                                <CardDescription>Choose from recently uploaded media assets.</CardDescription>
                            </CardHeader>
                            <CardContent className="grid gap-4">
                                {selectedImage && (
                                    <img
                                        src={selectedImage.url}
                                        alt={selectedImage.alt_text ?? selectedImage.filename}
                                        className="h-32 w-full rounded-lg border object-cover"
                                    />
                                )}
                                <select
                                    name="featured_image_id"
                                    defaultValue={defaults.featured_image_id ?? ''}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs"
                                >
                                    <option value="">No featured image</option>
                                    {mediaAssets.map((asset) => (
                                        <option key={asset.id} value={asset.id}>
                                            {asset.filename}
                                        </option>
                                    ))}
                                </select>
                            </CardContent>
                        </Card>
                    </div>
                </>
            )}
        </Form>
    );
}
