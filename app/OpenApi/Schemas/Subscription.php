<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Subscription',
    required: ['id', 'status', 'plan_id'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'tenant_id', type: 'integer', example: 1),
        new OA\Property(property: 'plan_id', type: 'integer', example: 2),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'trialing', 'cancelled', 'suspended'], example: 'active'),
        new OA\Property(property: 'quantity', type: 'integer', example: 1),
        new OA\Property(property: 'trial_ends_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(
            property: 'plan',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 2),
                new OA\Property(property: 'name', type: 'string', example: 'Professional'),
                new OA\Property(property: 'slug', type: 'string', example: 'professional'),
            ],
            type: 'object',
            nullable: true,
        ),
    ],
)]
class Subscription {}
