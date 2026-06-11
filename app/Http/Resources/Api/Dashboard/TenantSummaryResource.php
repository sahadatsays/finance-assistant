<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{id: int, name: string, slug: string, currency: string} $tenant */
        $tenant = $this->resource;

        return [
            'id' => $tenant['id'],
            'name' => $tenant['name'],
            'slug' => $tenant['slug'],
            'currency' => $tenant['currency'],
        ];
    }
}
