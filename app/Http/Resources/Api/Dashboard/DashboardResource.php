<?php

namespace App\Http\Resources\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{tenant: array<string, mixed>, metrics: array<string, mixed>, charts: array<string, mixed>} $dashboard */
        $dashboard = $this->resource;

        return [
            'tenant' => new TenantSummaryResource($dashboard['tenant']),
            'metrics' => new DashboardMetricsResource($dashboard['metrics']),
            'charts' => new DashboardChartsResource($dashboard['charts']),
        ];
    }
}
