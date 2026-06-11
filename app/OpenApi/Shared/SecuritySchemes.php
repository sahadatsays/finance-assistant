<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Laravel Sanctum bearer token obtained from POST /api/v1/auth/login',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
)]
#[OA\SecurityScheme(
    securityScheme: 'tenant',
    type: 'apiKey',
    description: 'Optional tenant workspace ID for multi-tenant SaaS requests',
    name: 'X-Tenant-Id',
    in: 'header',
)]
class SecuritySchemes {}
