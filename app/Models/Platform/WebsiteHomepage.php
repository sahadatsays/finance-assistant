<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $hero_eyebrow
 * @property string|null $hero_title
 * @property string|null $hero_subtitle
 * @property string|null $hero_primary_label
 * @property string|null $hero_primary_url
 * @property string|null $hero_secondary_label
 * @property string|null $hero_secondary_url
 * @property int|null $hero_image_id
 * @property array<int, array<string, string>>|null $statistics
 * @property array<int, array<string, string>>|null $features
 * @property array<int, array<string, string>>|null $cta_sections
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'hero_eyebrow', 'hero_title', 'hero_subtitle',
    'hero_primary_label', 'hero_primary_url',
    'hero_secondary_label', 'hero_secondary_url',
    'hero_image_id', 'statistics', 'features', 'cta_sections', 'is_active',
])]
class WebsiteHomepage extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'statistics' => 'array',
            'features' => 'array',
            'cta_sections' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<MediaAsset, $this>
     */
    public function heroImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'hero_image_id');
    }
}
