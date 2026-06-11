<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\Notification\StoreDeviceTokenRequest;
use App\Models\Platform\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends ApiController
{
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $token = DeviceToken::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token' => $request->validated('token'),
            ],
            [
                'platform' => $request->validated('platform'),
                'device_name' => $request->validated('device_name'),
                'last_used_at' => now(),
            ],
        );

        return $this->success(
            data: ['device_token' => ['id' => $token->id, 'platform' => $token->platform]],
            message: 'Device token registered successfully.',
            status: 201,
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string', 'max:512']]);

        DeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $request->input('token'))
            ->delete();

        return $this->success(message: 'Device token removed successfully.');
    }
}
