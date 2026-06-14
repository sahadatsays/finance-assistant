<?php

use App\Models\Platform\BlogPost;
use App\Models\Platform\Plan;

test('marketing home page renders', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Take control of your money')
        ->assertSee('Start Free');
});

test('marketing static pages render', function (string $routeName, string $expectedText) {
    $this->get(route($routeName))
        ->assertSuccessful()
        ->assertSee($expectedText);
})->with([
    ['marketing.features', 'Everything you need to manage money'],
    ['marketing.pricing', 'Simple pricing that grows with you'],
    ['marketing.about', 'We believe everyone deserves clarity'],
    ['marketing.contact', 'here to help'],
    ['marketing.blog', 'Personal finance tips'],
    ['marketing.help', 'Help Center'],
    ['marketing.privacy', 'Privacy Policy'],
    ['marketing.terms', 'Terms of Service'],
]);

test('pricing page shows active plans from database', function () {
    Plan::factory()->create([
        'name' => 'Starter',
        'slug' => 'starter-test',
        'price_monthly' => 4.99,
        'is_active' => true,
    ]);

    $this->get(route('marketing.pricing'))
        ->assertSuccessful()
        ->assertSee('Starter')
        ->assertSee('4.99');
});

test('blog article page renders for known slug', function () {
    $this->get(route('marketing.blog.show', 'how-to-start-budgeting'))
        ->assertSuccessful()
        ->assertSee('How to Start Budgeting in 30 Minutes')
        ->assertSee('List your income', false);
});

test('published blog post from database renders markdown body', function () {
    BlogPost::factory()->published()->create([
        'slug' => 'markdown-test-article',
        'title' => 'Markdown Test Article',
        'body' => "## Key takeaway\n\nTrack spending every week.",
    ]);

    $this->get(route('marketing.blog.show', 'markdown-test-article'))
        ->assertSuccessful()
        ->assertSee('Key takeaway')
        ->assertSee('Track spending every week');
});

test('blog article returns not found for unknown slug', function () {
    $this->get(route('marketing.blog.show', 'unknown-article'))
        ->assertNotFound();
});

test('help article page renders for known slugs', function () {
    $this->get(route('marketing.help.show', ['getting-started', 'create-account']))
        ->assertSuccessful()
        ->assertSee('Create your account');
});

test('help article returns not found for unknown category', function () {
    $this->get(route('marketing.help.show', ['unknown', 'create-account']))
        ->assertNotFound();
});

test('contact form validates required fields', function () {
    $this->post(route('marketing.contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});

test('contact form accepts valid submission', function () {
    $this->post(route('marketing.contact.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'general',
        'message' => 'I would like to learn more about Finance Assistant.',
    ])
        ->assertRedirect(route('marketing.contact'))
        ->assertSessionHas('success');
});

test('login page renders blade marketing auth layout', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('Welcome back')
        ->assertSee('Create one free');
});

test('register page renders with optional plan query', function () {
    $this->get(route('register', ['plan' => 'pro']))
        ->assertSuccessful()
        ->assertSee('Start managing your money for free')
        ->assertSee('Pro');
});
