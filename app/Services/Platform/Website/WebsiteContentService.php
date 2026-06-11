<?php

namespace App\Services\Platform\Website;

use App\Enums\Website\ContentStatus;
use App\Enums\Website\NavigationLocation;
use App\Models\Platform\BlogPost;
use App\Models\Platform\Faq;
use App\Models\Platform\FooterSetting;
use App\Models\Platform\NavigationItem;
use App\Models\Platform\Plan;
use App\Models\Platform\SeoEntry;
use App\Models\Platform\Testimonial;
use App\Models\Platform\WebsiteHomepage;
use App\Models\Platform\WebsitePage;
use Illuminate\Support\Collection;

class WebsiteContentService
{
    /**
     * @return array{title: string, description: string, keywords: array<int, string>, og_image_url: string|null, canonical_url: string|null}
     */
    public function seo(string $pageKey): array
    {
        $entry = SeoEntry::query()->with('ogImage')->where('page_key', $pageKey)->first();

        if ($entry !== null) {
            return [
                'title' => $entry->meta_title ?? config("marketing.seo.{$pageKey}.title", config('app.name')),
                'description' => $entry->meta_description ?? config("marketing.seo.{$pageKey}.description", ''),
                'keywords' => $entry->meta_keywords ?? [],
                'og_image_url' => $entry->ogImage?->url(),
                'canonical_url' => $entry->canonical_url,
            ];
        }

        $fallback = config("marketing.seo.{$pageKey}", config('marketing.seo.home'));

        return [
            'title' => $fallback['title'] ?? config('app.name'),
            'description' => $fallback['description'] ?? '',
            'keywords' => [],
            'og_image_url' => null,
            'canonical_url' => null,
        ];
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function activeTestimonials(): Collection
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($testimonials->isNotEmpty()) {
            return $testimonials;
        }

        return collect(config('marketing.testimonials', []))
            ->map(fn (array $item): object => (object) [
                'quote' => $item['quote'],
                'author_name' => $item['name'],
                'author_role' => $item['role'],
            ]);
    }

    /**
     * @return Collection<int, Faq>
     */
    public function activeFaqs(?string $category = 'pricing'): Collection
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('sort_order')
            ->get();

        if ($faqs->isNotEmpty()) {
            return $faqs;
        }

        return collect(config('marketing.pricing_faq', []))
            ->map(fn (array $item): object => (object) [
                'question' => $item['question'],
                'answer' => $item['answer'],
            ]);
    }

    /**
     * @return Collection<int, BlogPost|object>
     */
    public function publishedBlogPosts(): Collection
    {
        $posts = BlogPost::query()
            ->where('status', ContentStatus::Published)
            ->orderByDesc('published_at')
            ->get();

        if ($posts->isNotEmpty()) {
            return $posts;
        }

        return collect(config('marketing.blog_posts', []))
            ->map(fn (array $item): object => (object) $item);
    }

    public function publishedBlogPost(string $slug): BlogPost|\stdClass|null
    {
        $post = BlogPost::query()
            ->where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->first();

        if ($post !== null) {
            return $post;
        }

        $fallback = collect(config('marketing.blog_posts', []))->firstWhere('slug', $slug);

        return $fallback !== null ? (object) $fallback : null;
    }

    public function publishedPage(string $slug): ?WebsitePage
    {
        return WebsitePage::query()
            ->where('slug', $slug)
            ->where('status', ContentStatus::Published)
            ->first();
    }

    /**
     * @return Collection<int, NavigationItem>
     */
    public function navigation(NavigationLocation $location): Collection
    {
        $items = NavigationItem::query()
            ->where('location', $location)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with('children')
            ->get();

        return $items;
    }

    public function footerSettings(): FooterSetting
    {
        return FooterSetting::query()->firstOrCreate([], [
            'copyright_text' => 'Built for individuals, households & teams.',
            'tagline' => null,
            'show_newsletter' => false,
        ]);
    }

    public function homepage(): WebsiteHomepage
    {
        return WebsiteHomepage::query()->firstOrCreate([], [
            'hero_eyebrow' => 'Multi-tenant personal finance platform',
            'hero_title' => 'Take control of your money — without the spreadsheet chaos',
            'hero_subtitle' => 'Track transactions, master budgets, reach savings goals, and understand your net worth.',
            'hero_primary_label' => 'Start Free',
            'hero_primary_url' => '/register',
            'hero_secondary_label' => 'View Pricing',
            'hero_secondary_url' => '/pricing',
            'statistics' => [],
            'features' => [],
            'cta_sections' => [],
            'is_active' => true,
        ]);
    }

    /**
     * @return Collection<int, Plan>
     */
    public function activePlans(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price_monthly')
            ->get();
    }
}
