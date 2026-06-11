<?php

namespace App\Modules\Tenant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantUsageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'users_count' => $this->resource['users_count'],
            'owners_count' => $this->resource['owners_count'],
            'logins_last_30_days' => $this->resource['logins_last_30_days'],
            'last_activity_at' => $this->resource['last_activity_at'],
            'plan_slug' => $this->resource['plan_slug'],
            'plan_max_users' => $this->resource['plan_max_users'],
            'subscription_status' => $this->resource['subscription_status'],
        ];
    }
}
