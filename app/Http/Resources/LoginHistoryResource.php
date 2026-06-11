<?php

namespace App\Http\Resources;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoginHistory
 */
class LoginHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'login_method' => $this->login_method->value,
            'status' => $this->status->value,
            'failure_reason' => $this->failure_reason,
            'device' => new UserDeviceResource($this->whenLoaded('device')),
            'logged_in_at' => $this->logged_in_at,
        ];
    }
}
