<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Api\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\OpenApi\Shared\SanctumSecurityConfiguration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends ApiController
{
    #[OA\Get(
        path: '/auth/profile',
        operationId: 'getAuthProfile',
        summary: 'Get authenticated user profile',
        description: 'Returns the currently authenticated user and profile data. Requires a valid Sanctum bearer token.',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PROTECTED,
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profile retrieved successfully.'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
        ],
    )]
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return $this->success(
            data: ['user' => new UserResource($user)],
            message: 'Profile retrieved successfully.',
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $profileData = collect($validated)->only([
            'avatar_url',
            'phone',
            'timezone',
            'locale',
            'bio',
        ])->filter()->all();

        $user->fill(collect($validated)->only(['name', 'email'])->all());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($profileData !== []) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData,
            );
        }

        return $this->success(
            data: ['user' => new UserResource($user->load('profile'))],
            message: 'Profile updated successfully.',
        );
    }
}
