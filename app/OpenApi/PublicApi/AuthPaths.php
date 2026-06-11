<?php

namespace App\OpenApi\PublicApi;

use App\OpenApi\Shared\SanctumSecurityConfiguration;
use OpenApi\Attributes as OA;

class AuthPaths
{
    #[OA\Post(
        path: '/auth/register',
        operationId: 'registerUser',
        summary: 'Register a new user',
        description: 'Creates a user account and returns a Sanctum bearer token.',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PUBLIC,
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Jane Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'swagger-ui'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registration successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration successful.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/AuthTokenResponse'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/ServerError', response: 500),
        ],
    )]
    public function register(): void {}

    #[OA\Post(
        path: '/auth/forgot-password',
        operationId: 'forgotPassword',
        summary: 'Request password reset link',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PUBLIC,
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ],
            ),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
        ],
    )]
    public function forgotPassword(): void {}

    #[OA\Post(
        path: '/auth/reset-password',
        operationId: 'resetPassword',
        summary: 'Reset password with token',
        tags: ['Authentication'],
        security: SanctumSecurityConfiguration::PUBLIC,
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ],
            ),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
        ],
    )]
    public function resetPassword(): void {}
}
