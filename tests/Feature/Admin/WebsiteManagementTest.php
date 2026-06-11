<?php

use App\Models\Platform\Faq;
use App\Models\Platform\SeoEntry;
use App\Models\Platform\Testimonial;
use App\Models\Platform\WebsiteHomepage;
use App\Models\User;

test('super admin can view website management dashboard', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.website.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/website/index')
            ->has('stats')
            ->has('modules', 10));
});

test('super admin can access all website management modules', function () {
    $admin = User::factory()->platformAdmin()->create();

    $routes = [
        'admin.website.homepage.index',
        'admin.website.pages.index',
        'admin.website.navigation.index',
        'admin.website.footer.index',
        'admin.website.testimonials.index',
        'admin.website.faqs.index',
        'admin.website.plans.index',
        'admin.website.blog.index',
        'admin.website.seo.index',
        'admin.website.media.index',
    ];

    foreach ($routes as $route) {
        $this->actingAs($admin)->get(route($route))->assertOk();
    }
});

test('super admin can create testimonial and faq', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)->post(route('admin.website.testimonials.store'), [
        'quote' => 'Great product for our team.',
        'author_name' => 'Test User',
        'author_role' => 'Manager',
    ])->assertRedirect(route('admin.website.testimonials.index'));

    $this->assertDatabaseHas('testimonials', ['author_name' => 'Test User']);

    $this->actingAs($admin)->post(route('admin.website.faqs.store'), [
        'category' => 'pricing',
        'question' => 'Is there a free plan?',
        'answer' => 'Yes, the Free plan is available forever.',
    ])->assertRedirect(route('admin.website.faqs.index'));

    $this->assertDatabaseHas('faqs', ['question' => 'Is there a free plan?']);
});

test('super admin can update homepage content', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)->patch(route('admin.website.homepage.update'), [
        'hero_title' => 'Updated Hero Title',
        'hero_subtitle' => 'Updated subtitle text',
        'statistics' => [['label' => 'Users', 'value' => '1,000+']],
        'features' => [['title' => 'Budgets', 'description' => 'Track spending']],
        'cta_sections' => [['title' => 'Get started', 'subtitle' => 'Free to start']],
    ])->assertRedirect(route('admin.website.homepage.index'));

    expect(WebsiteHomepage::query()->first()->hero_title)->toBe('Updated Hero Title');
});

test('super admin can update seo entry', function () {
    $admin = User::factory()->platformAdmin()->create();
    $entry = SeoEntry::query()->create([
        'page_key' => 'home',
        'meta_title' => 'Old Title',
        'meta_description' => 'Old description',
    ]);

    $this->actingAs($admin)->patch(route('admin.website.seo.update', $entry), [
        'meta_title' => 'New SEO Title',
        'meta_description' => 'New SEO description',
        'meta_keywords' => 'finance, budget, saas',
        'canonical_url' => 'https://example.com',
    ])->assertRedirect(route('admin.website.seo.index'));

    $entry->refresh();
    expect($entry->meta_title)->toBe('New SEO Title')
        ->and($entry->meta_keywords)->toBe(['finance', 'budget', 'saas']);
});

test('marketing home uses cms homepage and testimonials', function () {
    WebsiteHomepage::query()->create([
        'hero_title' => 'CMS Driven Hero',
        'is_active' => true,
    ]);

    Testimonial::query()->create([
        'quote' => 'CMS testimonial quote',
        'author_name' => 'CMS Author',
        'author_role' => 'Tester',
        'is_active' => true,
    ]);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('CMS Driven Hero')
        ->assertSee('CMS testimonial quote');
});

test('marketing pricing uses cms faqs', function () {
    Faq::query()->create([
        'category' => 'pricing',
        'question' => 'CMS FAQ Question?',
        'answer' => 'CMS FAQ Answer.',
        'is_active' => true,
    ]);

    $this->get(route('marketing.pricing'))
        ->assertSuccessful()
        ->assertSee('CMS FAQ Question?');
});

test('non admin cannot access website management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.website.index'))
        ->assertForbidden();
});
