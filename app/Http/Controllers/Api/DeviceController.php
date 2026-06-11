<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateDeviceRequest;
use App\Http\Resources\UserDeviceResource;
use App\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->currentAccessToken()?->id;
        $currentSessionId = $request->hasSession() ? $request->session()->getId() : null;

        $devices = $request->user()
            ->devices()
            ->latest('last_active_at')
            ->get()
            ->each(function (UserDevice $device) use ($currentTokenId, $currentSessionId): void {
                $device->setAttribute(
                    'is_current',
                    ($currentTokenId !== null && $device->token_id === $currentTokenId)
                    || ($currentSessionId !== null && $device->session_id === $currentSessionId),
                );
            });

        return response()->json([
            'devices' => UserDeviceResource::collection($devices),
        ]);
    }

    public function update(UpdateDeviceRequest $request, UserDevice $device): JsonResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        $device->update($request->validated());

        return response()->json([
            'message' => 'Device updated successfully.',
            'device' => new UserDeviceResource($device),
        ]);
    }

    public function destroy(Request $request, UserDevice $device): JsonResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        if ($device->token_id !== null) {
            $request->user()->tokens()->where('id', $device->token_id)->delete();
        }

        $device->delete();

        return response()->json([
            'message' => 'Device revoked successfully.',
        ]);
    }
}
