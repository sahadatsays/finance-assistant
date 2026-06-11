<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

/**
 * Laravel Sanctum HTTP Bearer authentication for OpenAPI 3.0.
 *
 * Swagger UI workflow:
 * 1. POST /api/v1/auth/login (Public docs) → copy `data.token`
 * 2. Click Authorize → paste token (Swagger UI adds the Bearer prefix)
 * 3. Execute protected endpoints — lock icon indicates auth required
 */
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: <<<'DESC'
Laravel Sanctum personal access token.

**How to authenticate in Swagger UI:**
1. Call `POST /auth/login` in the Public API docs with valid credentials.
2. Copy the `token` value from `data.token` in the response.
3. Click the **Authorize** button (lock icon, top right).
4. Paste the token into the `sanctum` field (do not include "Bearer").
5. Click **Authorize**, then **Close**.

The token is sent as: `Authorization: Bearer {token}`

Demo credentials: `owner@acme.com` / `password`
DESC,
)]
#[OA\SecurityScheme(
    securityScheme: 'tenant',
    type: 'apiKey',
    name: 'X-Tenant-Id',
    in: 'header',
    description: 'Optional active tenant workspace ID when the user belongs to multiple tenants.',
)]
class SanctumSecurityConfiguration
{
    /** @var array<int, array<string, array<string>>> */
    public const PROTECTED = [['sanctum' => []]];

    /** @var array<int, array<string, array<string>>> */
    public const PROTECTED_WITH_TENANT = [['sanctum' => []], ['tenant' => []]];

    /** @var array<int, array<string, array<string>>> */
    public const PUBLIC = [];
}
