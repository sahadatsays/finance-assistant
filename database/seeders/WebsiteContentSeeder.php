<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->get()->each(function (Plan $plan, int $index): void {
            $plan->update(['sort_order' => $index]);
        });

        foreach (config('marketing.seo', []) as $pageKey => $seo) {
            SeoEntry::query()->updateOrCreate(
                ['page_key' => $pageKey],
                [
                    'meta_title' => $seo['title'],
                    'meta_description' => $seo['description'],
                    'meta_keywords' => [],
                ],
            );
        }

        foreach (config('marketing.testimonials', []) as $index => $item) {
            Testimonial::query()->updateOrCreate(
                ['author_name' => $item['name']],
                [
                    'quote' => $item['quote'],
                    'author_role' => $item['role'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }

        foreach (config('marketing.pricing_faq', []) as $index => $item) {
            Faq::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'category' => 'pricing',
                    'answer' => $item['answer'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }

        foreach (config('marketing.blog_posts', []) as $post) {
            BlogPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'] ?? null,
                    'category' => $post['category'],
                    'status' => ContentStatus::Published,
                    'read_time_minutes' => (int) str_replace(' min', '', $post['read_time']),
                    'published_at' => $post['date'],
                ],
            );
        }

        WebsiteHomepage::query()->firstOrCreate([], [
            'hero_eyebrow' => 'Multi-tenant personal finance platform',
            'hero_title' => 'Take control of your money — without the spreadsheet chaos',
            'hero_subtitle' => 'Track transactions, master budgets, reach savings goals, and understand your net worth. Free for individuals. Built for households and teams.',
            'hero_primary_label' => 'Start Free',
            'hero_primary_url' => '/register',
            'hero_secondary_label' => 'View Pricing',
            'hero_secondary_url' => '/pricing',
            'statistics' => [
                ['label' => 'Transactions tracked', 'value' => '10,000+'],
                ['label' => 'Active workspaces', 'value' => '500+'],
                ['label' => 'Uptime', 'value' => '99.9%'],
            ],
            'features' => [
                ['title' => 'See everything', 'description' => 'Accounts, transactions, and net worth in one dashboard'],
                ['title' => 'Stay on budget', 'description' => 'Monthly budgets with alerts before you overspend'],
                ['title' => 'Reach goals faster', 'description' => 'Savings goals with progress and forecasts'],
            ],
            'cta_sections' => [
                [
                    'title' => 'Ready to manage money with confidence?',
                    'subtitle' => 'Start free — no credit card required.',
                    'primary_label' => 'Start Free',
                    'primary_url' => '/register',
                ],
            ],
            'is_active' => true,
        ]);

        FooterSetting::query()->firstOrCreate([], [
            'copyright_text' => 'Built for individuals, households & teams.',
            'tagline' => 'Secure · Multi-tenant · GDPR-ready',
            'show_newsletter' => false,
            'trust_badges' => ['Bank-level encryption', 'Tenant-isolated data', 'Web + mobile API'],
        ]);

        $headerNav = [
            ['label' => 'Features', 'route_name' => 'marketing.features'],
            ['label' => 'Pricing', 'route_name' => 'marketing.pricing'],
            ['label' => 'Blog', 'route_name' => 'marketing.blog'],
            ['label' => 'Help', 'route_name' => 'marketing.help'],
            ['label' => 'About', 'route_name' => 'marketing.about'],
            ['label' => 'Contact', 'route_name' => 'marketing.contact'],
        ];

        foreach ($headerNav as $index => $item) {
            NavigationItem::query()->updateOrCreate(
                ['location' => NavigationLocation::Header, 'label' => $item['label']],
                [
                    'route_name' => $item['route_name'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }

        foreach (['privacy', 'terms', 'about'] as $slug) {
            WebsitePage::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => ucfirst($slug),
                    'status' => ContentStatus::Published,
                    'published_at' => now(),
                ],
            );
        }
    }
}
