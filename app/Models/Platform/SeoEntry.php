<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $page_key
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array<int, string>|null $meta_keywords
 * @property int|null $og_image_id
 * @property string|null $canonical_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['page_key', 'meta_title', 'meta_description', 'meta_keywords', 'og_image_id', 'canonical_url'])]
class SeoEntry extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
        ];
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_image_id');
    }
}
