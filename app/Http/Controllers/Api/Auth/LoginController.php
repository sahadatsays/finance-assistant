<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\LoginMethod;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Api\AuthTokenResource;
use App\Models\User;
use App\OpenApi\Shared\SanctumSecurityConfiguration;
use App\Services\Auth\DeviceTrackingService;
use App\Services\Auth\LoginHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class LoginController extends ApiController
{
    public function __construct(
        private DeviceTrackingService $deviceTracking,
        private LoginHistoryService $loginHistory,
    ) {}

    #[OA\Post(
        path: '/auth/login',
        operationId: 'loginUser',
        summary: 'Login and obtain Sanctum token',
        description: 'Authenticates a user and returns a Sanctum personal access token. Copy `data.token` from the response, click **Authorize** in the Authenticated docs, and paste the token (Swagger UI adds the Bearer prefix automatically).',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PUBLIC,
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'owner@acme.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'swagger-ui'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResponse'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
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
