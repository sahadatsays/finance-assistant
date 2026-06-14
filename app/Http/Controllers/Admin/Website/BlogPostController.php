<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\Website\ContentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Website\StoreBlogPostRequest;
use App\Http\Requests\Admin\Website\UpdateBlogPostRequest;
use App\Http\Resources\Admin\BlogPostResource;
use App\Models\Platform\BlogPost;
use App\Models\Platform\MediaAsset;
use App\Services\Platform\ActivityLogService;
use App\Services\Platform\Website\BlogPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
        private BlogPostService $blogPosts,
    ) {}

    public function index(Request $request): Response
    {
        $query = BlogPost::query()
            ->with(['author', 'featuredImage'])
            ->orderByDesc('updated_at');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->where('category', $category);
        }

        $posts = $query->paginate(12)->withQueryString();

        return Inertia::render('admin/website/blog/index', [
            'posts' => BlogPostResource::collection($posts),
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
                'category' => $category ?? '',
            ],
            'stats' => [
                'total' => BlogPost::query()->count(),
                'published' => BlogPost::query()->where('status', ContentStatus::Published)->count(),
                'draft' => BlogPost::query()->where('status', ContentStatus::Draft)->count(),
            ],
            'categories' => config('marketing.blog_categories', []),
            'statuses' => array_column(ContentStatus::cases(), 'value'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/website/blog/create', $this->formProps());
    }

    public function edit(BlogPost $blogPost): Response
    {
        $blogPost->load(['author', 'featuredImage']);

        return Inertia::render('admin/website/blog/edit', [
            ...$this->formProps(),
            'post' => (new BlogPostResource($blogPost))->resolve(),
        ]);
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $validated = $this->blogPosts->prepareForCreate($request->validated(), $request->user());

        $post = BlogPost::query()->create($validated);

        $this->activityLog->log("Blog post \"{$post->title}\" was created.", subject: $post, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post created.')]);

        return to_route('admin.website.blog.edit', $post);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $validated = $this->blogPosts->prepareForUpdate($blogPost, $request->validated());

        $blogPost->update($validated);

        $this->activityLog->log("Blog post \"{$blogPost->title}\" was updated.", subject: $blogPost, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post updated.')]);

        return to_route('admin.website.blog.edit', $blogPost);
    }

    public function publish(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $this->blogPosts->publish($blogPost);

        $this->activityLog->log("Blog post \"{$blogPost->title}\" was published.", subject: $blogPost, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post published.')]);

        return back();
    }

    public function unpublish(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $this->blogPosts->unpublish($blogPost);

        $this->activityLog->log("Blog post \"{$blogPost->title}\" was unpublished.", subject: $blogPost, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post moved to draft.')]);

        return back();
    }

    public function destroy(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $title = $blogPost->title;
        $blogPost->delete();

        $this->activityLog->log("Blog post \"{$title}\" was deleted.", causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post deleted.')]);

        return to_route('admin.website.blog.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(): array
    {
        return [
            'categories' => config('marketing.blog_categories', []),
            'statuses' => array_column(ContentStatus::cases(), 'value'),
            'mediaAssets' => MediaAsset::query()
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (MediaAsset $asset): array => [
                    'id' => $asset->id,
                    'filename' => $asset->filename,
                    'url' => $asset->url(),
                    'alt_text' => $asset->alt_text,
                ]),
        ];
    }
}
