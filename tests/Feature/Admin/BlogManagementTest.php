<?php

use App\Enums\Website\ContentStatus;
use App\Models\Platform\BlogPost;
use App\Models\User;

test('super admin can view blog management index with stats and filters', function () {
    $admin = User::factory()->platformAdmin()->create();
    BlogPost::factory()->count(2)->published()->create();
    BlogPost::factory()->create(['title' => 'Draft Article']);

    $this->actingAs($admin)
        ->get(route('admin.website.blog.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/website/blog/index')
            ->has('posts.data', 3)
            ->has('stats')
            ->where('stats.published', 2)
            ->where('stats.draft', 1)
            ->has('categories')
            ->has('filters'));
});

test('super admin can filter blog posts by search and status', function () {
    $admin = User::factory()->platformAdmin()->create();
    BlogPost::factory()->published()->create(['title' => 'Budgeting Basics']);
    BlogPost::factory()->create(['title' => 'Hidden Draft']);

    $this->actingAs($admin)
        ->get(route('admin.website.blog.index', ['search' => 'Budgeting', 'status' => ContentStatus::Published->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Budgeting Basics'));
});

test('super admin can create edit publish and delete blog posts', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->get(route('admin.website.blog.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/website/blog/create'));

    $this->actingAs($admin)->post(route('admin.website.blog.store'), [
        'title' => 'Professional Blog Post',
        'category' => 'Guides',
        'status' => ContentStatus::Draft->value,
        'excerpt' => 'A short summary.',
        'body' => "## Hello\n\nThis is **markdown** content.",
    ])->assertRedirect();

    $post = BlogPost::query()->where('title', 'Professional Blog Post')->first();
    expect($post)->not->toBeNull()
        ->and($post->slug)->toBe('professional-blog-post')
        ->and($post->author_id)->toBe($admin->id)
        ->and($post->read_time_minutes)->toBeGreaterThan(0);

    $this->actingAs($admin)
        ->get(route('admin.website.blog.edit', $post))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/website/blog/edit')
            ->where('post.title', 'Professional Blog Post'));

    $this->actingAs($admin)->patch(route('admin.website.blog.update', $post), [
        'title' => 'Updated Blog Post',
        'meta_title' => 'SEO Title',
        'meta_description' => 'SEO description for the article.',
    ])->assertRedirect(route('admin.website.blog.edit', $post));

    $post->refresh();
    expect($post->title)->toBe('Updated Blog Post')
        ->and($post->meta_title)->toBe('SEO Title');

    $this->actingAs($admin)
        ->post(route('admin.website.blog.publish', $post))
        ->assertRedirect();

    $post->refresh();
    expect($post->status)->toBe(ContentStatus::Published)
        ->and($post->published_at)->not->toBeNull();

    $this->get(route('marketing.blog.show', $post->slug))
        ->assertSuccessful()
        ->assertSee('Updated Blog Post')
        ->assertSee('markdown');

    $this->actingAs($admin)
        ->post(route('admin.website.blog.unpublish', $post))
        ->assertRedirect();

    $post->refresh();
    expect($post->status)->toBe(ContentStatus::Draft);

    $this->get(route('marketing.blog.show', $post->slug))->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('admin.website.blog.destroy', $post))
        ->assertRedirect(route('admin.website.blog.index'));

    $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
});

test('blog store validates category against configured list', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)->post(route('admin.website.blog.store'), [
        'title' => 'Invalid Category Post',
        'category' => 'Not A Real Category',
        'status' => ContentStatus::Draft->value,
    ])->assertSessionHasErrors('category');
});
