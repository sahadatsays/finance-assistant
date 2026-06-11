<?php

namespace App\OpenApi\Admin;

use OpenApi\Attributes as OA;

class AdminPaths
{
    #[OA\Get(
        path: '/dashboard',
        operationId: 'adminDashboard',
        summary: 'Platform admin dashboard',
        description: 'Returns platform-wide tenant, subscription, and usage metrics.',
        tags: ['Admin Dashboard'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/Unauthorized', response: 401),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
    public function dashboard(): void {}

    #[OA\Get(
        path: '/tenants',
        operationId: 'adminListTenants',
        summary: 'List tenants',
        tags: ['Admin Tenants'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(ref: '#/components/parameters/Page'), new OA\Parameter(ref: '#/components/parameters/PerPage')],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated tenant list',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedEnvelope'),
            ),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
    public function listTenants(): void {}

    #[OA\Post(
        path: '/tenants',
        operationId: 'adminCreateTenant',
        summary: 'Create tenant',
        tags: ['Admin Tenants'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'owner_email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'New Corp'),
                    new OA\Property(property: 'owner_email', type: 'string', format: 'email'),
                    new OA\Property(property: 'plan_id', type: 'integer', example: 2),
                ],
            ),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 201),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
            new OA\Response(ref: '#/components/responses/Forbidden', response: 403),
        ],
    )]
    public function createTenant(): void {}

    #[OA\Get(
        path: '/tenants/{id}',
        operationId: 'adminShowTenant',
        summary: 'Show tenant',
        tags: ['Admin Tenants'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tenant details with subscription',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'tenant', ref: '#/components/schemas/Tenant'),
                                new OA\Property(property: 'subscription', ref: '#/components/schemas/Subscription'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function showTenant(): void {}

    #[OA\Post(
        path: '/tenants/{id}/suspend',
        operationId: 'adminSuspendTenant',
        summary: 'Suspend tenant',
        tags: ['Admin Tenants'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/NotFound', response: 404),
        ],
    )]
    public function suspendTenant(): void {}

    #[OA\Patch(
        path: '/tenants/{id}/subscription',
        operationId: 'adminUpdateSubscription',
        summary: 'Update tenant subscription',
        tags: ['Admin Tenants'],
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'plan_id', type: 'integer'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'trialing', 'cancelled', 'suspended']),
                ],
            ),
        ),
        responses: [
            new OA\Response(ref: '#/components/responses/Success', response: 200),
            new OA\Response(ref: '#/components/responses/ValidationError', response: 422),
        ],
    )]
    public function updateSubscription(): void {}
}
