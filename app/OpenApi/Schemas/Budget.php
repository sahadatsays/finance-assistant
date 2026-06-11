<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Budget',
    required: ['id', 'name', 'period_type', 'amount'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 3),
        new OA\Property(property: 'name', type: 'string', example: 'Monthly Budget'),
        new OA\Property(property: 'period_type', type: 'string', enum: ['monthly', 'weekly'], example: 'monthly'),
        new OA\Property(property: 'period_start', type: 'string', format: 'date', example: '2026-06-01'),
        new OA\Property(property: 'period_end', type: 'string', format: 'date', example: '2026-06-30'),
        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 3500),
        new OA\Property(
            property: 'utilization',
            properties: [
                new OA\Property(property: 'spent', type: 'number', example: 2180.5),
                new OA\Property(property: 'remaining', type: 'number', example: 1319.5),
                new OA\Property(property: 'percentage', type: 'number', example: 62.3),
                new OA\Property(property: 'status', type: 'string', example: 'on_track'),
            ],
            type: 'object',
        ),
    ],
)]
class Budget {}
