<?php

namespace App\Models\Platform;

use App\Enums\Website\NavigationLocation;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/**
 * @property int $id
 * @property NavigationLocation $location
 * @property string $label
 * @property string|null $url
 * @property string|null $route_name
 * @property int|null $parent_id
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $opens_in_new_tab
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['location', 'label', 'url', 'route_name', 'parent_id', 'sort_order', 'is_active', 'opens_in_new_tab'])]
class NavigationItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location' => NavigationLocation::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'opens_in_new_tab' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<NavigationItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<NavigationItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function resolvedUrl(): ?string
    {
        if ($this->route_name !== null && Route::has($this->route_name)) {
            return route($this->route_name);
        }

        return $this->url;
    }
}
