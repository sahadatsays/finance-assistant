<?php

namespace App\Http\Controllers\Admin\Website;

use App\Http\Controllers\Controller;
use App\Models\Platform\BlogPost;
use App\Models\Platform\Faq;
use App\Models\Platform\MediaAsset;
use App\Models\Platform\NavigationItem;
use App\Models\Platform\Plan;
use App\Models\Platform\SeoEntry;
use App\Models\Platform\Testimonial;
use App\Models\Platform\WebsitePage;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/website/index', [
            'stats' => [
                'pages' => WebsitePage::query()->count(),
                'navigation' => NavigationItem::query()->count(),
                'testimonials' => Testimonial::query()->count(),
                'faqs' => Faq::query()->count(),
                'plans' => Plan::query()->count(),
                'blog_posts' => BlogPost::query()->count(),
                'seo_entries' => SeoEntry::query()->count(),
                'media_assets' => MediaAsset::query()->count(),
            ],
            'modules' => [
                ['title' => 'Homepage Builder', 'description' => 'Hero, statistics, features, and CTA sections', 'href' => '/admin/website/homepage', 'icon' => 'home'],
                ['title' => 'Website Pages', 'description' => 'Manage static marketing pages', 'href' => '/admin/website/pages', 'icon' => 'file'],
                ['title' => 'Navigation Menu', 'description' => 'Header navigation links', 'href' => '/admin/website/navigation', 'icon' => 'menu'],
                ['title' => 'Footer Management', 'description' => 'Footer links and site-wide footer settings', 'href' => '/admin/website/footer', 'icon' => 'layout'],
                ['title' => 'Testimonials', 'description' => 'Customer quotes for the homepage', 'href' => '/admin/website/testimonials', 'icon' => 'quote'],
                ['title' => 'FAQs', 'description' => 'Pricing and support FAQs', 'href' => '/admin/website/faqs', 'icon' => 'help'],
                ['title' => 'Pricing Plans', 'description' => 'Create, update, and reorder subscription plans', 'href' => '/admin/website/plans', 'icon' => 'credit'],
                ['title' => 'Blog Posts', 'description' => 'Publish articles and product updates', 'href' => '/admin/website/blog', 'icon' => 'pen'],
                ['title' => 'SEO Management', 'description' => 'Meta titles, descriptions, and Open Graph images', 'href' => '/admin/website/seo', 'icon' => 'search'],
                ['title' => 'Media Library', 'description' => 'Upload and manage website images', 'href' => '/admin/website/media', 'icon' => 'image'],
            ],
        ]);
    }
}
