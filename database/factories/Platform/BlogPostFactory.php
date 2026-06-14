<?php

namespace Database\Factories\Platform;

use App\Enums\Website\ContentStatus;
use App\Models\Platform\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('###'),
            'title' => $title,
            'excerpt' => fake()->paragraph(),
            'body' => "## Overview\n\n".fake()->paragraphs(3, true),
            'meta_title' => null,
            'meta_description' => null,
            'category' => fake()->randomElement(config('marketing.blog_categories', ['Guides'])),
            'status' => ContentStatus::Draft,
            'read_time_minutes' => fake()->numberBetween(3, 12),
            'featured_image_id' => null,
            'author_id' => User::factory(),
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => ContentStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
