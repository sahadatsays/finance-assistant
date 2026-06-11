<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class MarketingController extends Controller
{
    public function home(): View
    {
        return $this->view('marketing.home', [
            'plans' => $this->activePlans(),
            'testimonials' => config('marketing.testimonials'),
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
        return $this->view('marketing.blog', [
            'posts' => config('marketing.blog_posts'),
        ]);
    }

    public function blogShow(string $slug): View|RedirectResponse
    {
        $post = collect(config('marketing.blog_posts'))
            ->firstWhere('slug', $slug);

        if ($post === null) {
            abort(404);
        }

        return $this->view('marketing.blog-show', [
            'post' => $post,
            'seo' => [
                'title' => $post['title'],
                'description' => $post['excerpt'],
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

        return view($template, array_merge([
            'seo' => config("marketing.seo.{$page}", config('marketing.seo.home')),
        ], $data));
    }

    /**
     * @return Collection<int, Plan>
     */
    private function activePlans()
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price_monthly')
            ->get();
    }
}
