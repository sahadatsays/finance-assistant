<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\LoginMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(
        private DeviceTrackingService $deviceTracking,
        private LoginHistoryService $loginHistory,
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        UserProfile::query()->create([
            'user_id' => $user->id,
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken($validated['device_name'] ?? 'api-token');
        $device = $this->deviceTracking->track(
            $user,
            $request,
            $token->accessToken->id,
            deviceName: $validated['device_name'] ?? null,
        );

        $this->loginHistory->recordSuccess($user, $request, LoginMethod::ApiToken, $device);

        $user->load('profile');

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ], 201);
    }
}
