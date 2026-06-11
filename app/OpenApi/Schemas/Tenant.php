<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Tenant',
    required: ['id', 'name', 'slug', 'currency'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Acme Corporation'),
        new OA\Property(property: 'slug', type: 'string', example: 'acme-corp'),
        new OA\Property(property: 'currency', type: 'string', example: 'USD'),
        new OA\Property(property: 'status', type: 'string', example: 'active'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
class Tenant {}
