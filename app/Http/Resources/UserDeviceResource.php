<?php

namespace App\Http\Resources;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserDevice
 */
class UserDeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'platform' => $this->platform,
            'browser' => $this->browser,
            'ip_address' => $this->ip_address,
            'is_trusted' => $this->is_trusted,
            'last_active_at' => $this->last_active_at,
            'is_current' => $this->when(
                isset($this->is_current),
                (bool) $this->is_current,
            ),
            'created_at' => $this->created_at,
        ];
    }
}
