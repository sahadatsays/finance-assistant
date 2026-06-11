<?php

namespace App\Modules\Finance\Resources;

use App\Models\Finance\Category;
use App\Modules\Finance\Enums\CategoryKind;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'color' => $this->color,
            'icon' => $this->icon,
            'kind' => CategoryKind::fromSystemFlag($this->is_system)->value,
            'is_system' => $this->is_system,
            'is_active' => $this->is_active,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'transactions_count' => $this->whenCounted('transactions'),
        ];
    }
}
