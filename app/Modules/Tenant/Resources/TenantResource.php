<?php

namespace App\Modules\Tenant\Resources;

use App\Models\Platform\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class TenantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status->value,
            'settings' => $this->settings,
            'trial_ends_at' => $this->trial_ends_at,
            'suspended_at' => $this->suspended_at,
            'users_count' => $this->whenCounted('tenantUsers'),
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
            'usage' => $this->when(isset($this->usage), $this->usage),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
