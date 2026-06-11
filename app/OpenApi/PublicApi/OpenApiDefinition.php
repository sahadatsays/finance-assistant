<?php

namespace App\OpenApi\PublicApi;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    security: [],
)]
#[OA\Info(
    version: '1.0.0',
    title: 'Finance Assistant API — Public',
    description: <<<'DESC'
Public foundation and authentication endpoints for the Finance Assistant multi-tenant SaaS platform.

**Start here for Swagger authentication:** Call `POST /auth/login`, copy `data.token`, then use **Authorize** in the Authenticated or Admin docs.
DESC,
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\Tag(name: 'Foundation', description: 'API metadata and health checks')]
#[OA\Tag(name: 'Authentication', description: 'Registration, login, and password reset')]
class OpenApiDefinition {}
