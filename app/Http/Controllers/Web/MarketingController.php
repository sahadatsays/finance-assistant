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
        $posts = $this->content->publishedBlogPosts()->map(function (mixed $post): array {
            if ($post instanceof BlogPost) {
                return [
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'excerpt' => $post->excerpt,
                    'category' => $post->category,
                    'date' => $post->published_at?->toDateString() ?? $post->created_at?->toDateString(),
                    'read_time' => "{$post->read_time_minutes} min",
                ];
            }

            return (array) $post;
        });

        return $this->view('marketing.blog', ['posts' => $posts]);
    }

    public function blogShow(string $slug): View|RedirectResponse
    {
        $post = $this->content->publishedBlogPost($slug);

        if ($post === null) {
            abort(404);
        }

        $isModel = $post instanceof BlogPost;

        return $this->view('marketing.blog-show', [
            'post' => $isModel ? [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'category' => $post->category,
                'date' => $post->published_at?->toDateString() ?? $post->created_at?->toDateString(),
                'read_time' => "{$post->read_time_minutes} min",
                'body' => $post->body,
            ] : (array) $post,
            'seo' => [
                'title' => $isModel ? $post->title : $post->title,
                'description' => $isModel ? ($post->excerpt ?? '') : $post->excerpt,
            ],
        ]);
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
