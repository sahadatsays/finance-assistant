<?php

namespace App\Http\Controllers\Admin\Website;

use App\Enums\Website\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Platform\BlogPost;
use App\Services\Platform\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): Response
    {
        return Inertia::render('admin/website/blog/index', [
            'posts' => BlogPost::query()->orderByDesc('created_at')->get(),
            'statuses' => array_column(ContentStatus::cases(), 'value'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique(BlogPost::class)],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'read_time_minutes' => ['integer', 'min:1', 'max:120'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media_assets,id'],
        ]);

        $post = BlogPost::query()->create([
            ...$validated,
            'author_id' => $request->user()->id,
            'published_at' => $validated['status'] === ContentStatus::Published->value ? now() : null,
        ]);

        $this->activityLog->log("Blog post \"{$post->title}\" was created.", subject: $post, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post created.')]);

        return to_route('admin.website.blog.index');
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $validated = $request->validate([
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'alpha_dash', Rule::unique(BlogPost::class)->ignore($blogPost->id)],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'status' => ['sometimes', 'required', Rule::enum(ContentStatus::class)],
            'read_time_minutes' => ['integer', 'min:1', 'max:120'],
            'featured_image_id' => ['nullable', 'integer', 'exists:media_assets,id'],
        ]);

        if (isset($validated['status']) && $validated['status'] === ContentStatus::Published->value && $blogPost->published_at === null) {
            $validated['published_at'] = now();
        }

        $blogPost->update($validated);

        $this->activityLog->log("Blog post \"{$blogPost->title}\" was updated.", subject: $blogPost, causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post updated.')]);

        return to_route('admin.website.blog.index');
    }

    public function destroy(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $title = $blogPost->title;
        $blogPost->delete();

        $this->activityLog->log("Blog post \"{$title}\" was deleted.", causer: $request->user());
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Blog post deleted.')]);

        return to_route('admin.website.blog.index');
    }
}
