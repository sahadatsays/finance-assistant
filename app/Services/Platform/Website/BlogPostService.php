<?php

namespace App\Services\Platform\Website;

use App\Enums\Website\ContentStatus;
use App\Models\Platform\BlogPost;
use App\Models\User;
use Illuminate\Support\Str;

class BlogPostService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForCreate(array $data, User $author): array
    {
        $data['author_id'] = $author->id;
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $this->slugFromTitle($data['title'] ?? ''));
        $data['read_time_minutes'] = $this->estimateReadTime($data['body'] ?? $data['excerpt'] ?? '');
        $data['published_at'] = ($data['status'] ?? ContentStatus::Draft->value) === ContentStatus::Published->value
            ? now()
            : null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForUpdate(BlogPost $post, array $data): array
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($this->slugFromTitle($data['title']), ignoreId: $post->id);
        }

        if (isset($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], ignoreId: $post->id);
        }

        if (array_key_exists('body', $data) || array_key_exists('excerpt', $data)) {
            $data['read_time_minutes'] = $this->estimateReadTime(
                $data['body'] ?? $post->body ?? $data['excerpt'] ?? $post->excerpt ?? '',
            );
        }

        if (isset($data['status'])) {
            if ($data['status'] === ContentStatus::Published->value && $post->published_at === null) {
                $data['published_at'] = now();
            }

            if ($data['status'] === ContentStatus::Draft->value) {
                $data['published_at'] = null;
            }
        }

        return $data;
    }

    public function publish(BlogPost $post): BlogPost
    {
        $post->update([
            'status' => ContentStatus::Published,
            'published_at' => $post->published_at ?? now(),
        ]);

        return $post->fresh();
    }

    public function unpublish(BlogPost $post): BlogPost
    {
        $post->update([
            'status' => ContentStatus::Draft,
        ]);

        return $post->fresh();
    }

    public function slugFromTitle(string $title): string
    {
        return Str::slug($title);
    }

    public function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'post';
        $candidate = $base;
        $suffix = 1;

        while ($this->slugExists($candidate, $ignoreId)) {
            $candidate = "{$base}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    public function estimateReadTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / 200));
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return BlogPost::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
