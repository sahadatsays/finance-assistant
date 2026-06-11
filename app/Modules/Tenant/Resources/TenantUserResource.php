<?php

namespace App\Modules\Tenant\Resources;

use App\Http\Resources\UserResource;
use App\Models\Platform\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TenantUser
 */
class TenantUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role->value,
            'invited_at' => $this->invited_at,
            'joined_at' => $this->joined_at,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
