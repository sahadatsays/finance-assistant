<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\LoginMethod;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\AuthTokenResource;
use App\Models\User;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends ApiController
{
    public function __construct(
        private DeviceTrackingService $deviceTracking,
        private LoginHistoryService $loginHistory,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        $token = $user->createToken($request->input('device_name', 'api-token'));
        $device = $this->deviceTracking->track(
            $user,
            $request,
            $token->accessToken->id,
            deviceName: $request->input('device_name'),
        );

        $this->loginHistory->recordSuccess($user, $request, LoginMethod::ApiToken, $device);

        return $this->success(
            data: AuthTokenResource::toArray($user, $token->plainTextToken),
            message: 'Login successful.',
        );
    }
}
