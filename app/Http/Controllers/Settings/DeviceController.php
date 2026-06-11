<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateDeviceRequest;
use App\Http\Resources\UserDeviceResource;
use App\Models\UserDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function index(Request $request): Response
    {
        $currentSessionId = $request->session()->getId();

        $devices = $request->user()
            ->devices()
            ->latest('last_active_at')
            ->get()
            ->each(function (UserDevice $device) use ($currentSessionId): void {
                $device->setAttribute(
                    'is_current',
                    $device->session_id === $currentSessionId,
                );
            });

        return Inertia::render('settings/devices', [
            'devices' => UserDeviceResource::collection($devices)->resolve(),
        ]);
    }

    public function update(UpdateDeviceRequest $request, UserDevice $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        $device->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Device updated.')]);

        return to_route('devices.index');
    }

    public function destroy(Request $request, UserDevice $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        if ($device->session_id === $request->session()->getId()) {
            return back()->withErrors([
                'device' => __('You cannot revoke your current device.'),
            ]);
        }

        if ($device->token_id !== null) {
            $request->user()->tokens()->where('id', $device->token_id)->delete();
        }

        $device->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Device revoked.')]);

        return to_route('devices.index');
    }
}
