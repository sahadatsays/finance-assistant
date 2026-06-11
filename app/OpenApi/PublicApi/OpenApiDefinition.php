<?php

namespace App\OpenApi\PublicApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(openapi: '3.0.0')]
#[OA\Info(
    version: '1.0.0',
    title: 'Finance Assistant API — Public',
    description: 'Public foundation and authentication endpoints for the Finance Assistant multi-tenant SaaS platform. No authentication required for login and registration.',
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\Tag(name: 'Foundation', description: 'API metadata and health checks')]
#[OA\Tag(name: 'Authentication', description: 'Registration, login, and password reset')]
class OpenApiDefinition {}
