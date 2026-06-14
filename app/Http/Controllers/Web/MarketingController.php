<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Platform\BlogPost;
use App\Services\Platform\Website\WebsiteContentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MarketingController extends Controller
{
    public function __construct(private WebsiteContentService $content) {}

    public function home(): View
    {
        $homepage = $this->content->homepage()->load('heroImage');

        return $this->view('marketing.home', [
            'homepage' => $homepage,
            'plans' => $this->content->activePlans(),
            'testimonials' => $this->content->activeTestimonials(),
        ]);
    }

    public function features(): View
    {
        return $this->view('marketing.features', [
            'featureCategories' => config('marketing.feature_categories'),
        ]);
    }

    public function about(): View
    {
        return $this->view('marketing.about');
    }

    public function blog(): View
    {
        $category = request()->string('category')->trim()->toString();

        $posts = $this->content->publishedBlogPosts()
            ->when($category !== '', fn ($collection) => $collection->filter(
                fn (mixed $post): bool => ($post instanceof BlogPost ? $post->category : $post->category) === $category,
            ))
            ->map(fn (mixed $post): array => $this->formatBlogPostSummary($post))
            ->values();

        return $this->view('marketing.blog', [
            'posts' => $posts,
            'categories' => config('marketing.blog_categories', []),
            'activeCategory' => $category,
        ]);
    }

    public function blogShow(string $slug): View|RedirectResponse
    {
        $post = $this->content->publishedBlogPost($slug);

        if ($post === null) {
            abort(404);
        }

        $isModel = $post instanceof BlogPost;

        $formatted = $isModel ? $this->formatBlogPostDetail($post) : (array) $post;

        return $this->view('marketing.blog-show', [
            'post' => $formatted,
            'seo' => [
                'title' => $isModel ? ($post->meta_title ?: $post->title) : ($post->title ?? ''),
                'description' => $isModel
                    ? ($post->meta_description ?: $post->excerpt ?: '')
                    : ($post->excerpt ?? ''),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBlogPostSummary(mixed $post): array
    {
        if ($post instanceof BlogPost) {
            return [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'category' => $post->category,
                'date' => $post->published_at?->toDateString() ?? $post->created_at?->toDateString(),
                'read_time' => "{$post->read_time_minutes} min",
                'featured_image_url' => $post->featuredImage?->url(),
                'author_name' => $post->author?->name,
            ];
        }

        return (array) $post;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatBlogPostDetail(BlogPost $post): array
    {
        return [
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'category' => $post->category,
            'date' => $post->published_at?->toDateString() ?? $post->created_at?->toDateString(),
            'read_time' => "{$post->read_time_minutes} min",
            'body' => $post->body,
            'featured_image_url' => $post->featuredImage?->url(),
            'author_name' => $post->author?->name,
        ];
    }

    public function help(): View
    {
        return $this->view('marketing.help', [
            'categories' => config('marketing.help_categories'),
        ]);
    }

    public function helpShow(string $category, string $article): View
    {
        $categoryData = collect(config('marketing.help_categories'))
            ->firstWhere('slug', $category);

        if ($categoryData === null) {
            abort(404);
        }

        $articleData = collect($categoryData['articles'])
            ->firstWhere('slug', $article);

        if ($articleData === null) {
            abort(404);
        }

        return $this->view('marketing.help-show', [
            'category' => $categoryData,
            'article' => $articleData,
            'seo' => [
                'title' => $articleData['title'],
                'description' => "Help article: {$articleData['title']}",
            ],
        ]);
    }

    public function privacy(): View
    {
        return $this->view('marketing.privacy');
    }

    public function terms(): View
    {
        return $this->view('marketing.terms');
    }

    public function contact(): View
    {
        return $this->view('marketing.contact', [
            'subjects' => config('marketing.contact_subjects'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function view(string $template, array $data = []): View
    {
        $page = str_replace(['marketing.', '-'], ['', '_'], basename($template, '.blade.php'));
        $seo = $this->content->seo($page);

        return view($template, array_merge([
            'seo' => [
                'title' => $seo['title'],
                'description' => $seo['description'],
            ],
        ], $data));
    }
}
