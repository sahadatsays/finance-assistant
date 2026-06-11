<?php

namespace App\OpenApi\Admin;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    security: [['sanctum' => []]],
)]
#[OA\Info(
    version: '1.0.0',
    title: 'Finance Assistant API — Admin',
    description: 'Platform administrator APIs for tenant lifecycle and subscription management. Requires Sanctum bearer token with platform admin privileges. Obtain a token via `POST /auth/login` in the Public API docs.',
)]
#[OA\Server(url: '/api/v1/admin', description: 'Admin API v1')]
#[OA\Tag(name: 'Admin Dashboard', description: 'Platform-wide metrics')]
#[OA\Tag(name: 'Admin Tenants', description: 'Tenant provisioning and lifecycle')]
class OpenApiDefinition {}
