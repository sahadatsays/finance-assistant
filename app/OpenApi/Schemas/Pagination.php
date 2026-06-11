<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationMeta',
    required: ['current_page', 'last_page', 'per_page', 'total'],
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 5),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'total', type: 'integer', example: 72),
        new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'to', type: 'integer', example: 15, nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'PaginatedEnvelope',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: 'meta',
                    properties: [
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                    ],
                    type: 'object',
                ),
            ],
        ),
    ],
)]
class Pagination {}
