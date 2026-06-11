<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $copyright_text
 * @property string|null $tagline
 * @property bool $show_newsletter
 * @property string|null $newsletter_heading
 * @property array<int, string>|null $trust_badges
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['copyright_text', 'tagline', 'show_newsletter', 'newsletter_heading', 'trust_badges'])]
class FooterSetting extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_newsletter' => 'boolean',
            'trust_badges' => 'array',
        ];
    }
}
