<?php

namespace App\Models\Platform;

use App\Enums\Website\ContentStatus;
use App\Models\User;
use Database\Factories\Platform\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $excerpt
 * @property string|null $body
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string $category
 * @property ContentStatus $status
 * @property int $read_time_minutes
 * @property int|null $featured_image_id
 * @property int|null $author_id
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slug', 'title', 'excerpt', 'body', 'meta_title', 'meta_description', 'category', 'status', 'read_time_minutes', 'featured_image_id', 'author_id', 'published_at'])]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
            'read_time_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'featured_image_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function isPublished(): bool
    {
        return $this->status === ContentStatus::Published;
    }
}
