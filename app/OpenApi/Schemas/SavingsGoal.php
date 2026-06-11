<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SavingsGoal',
    required: ['id', 'name', 'type', 'target_amount', 'current_amount'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 7),
        new OA\Property(property: 'name', type: 'string', example: 'Japan Trip'),
        new OA\Property(property: 'type', type: 'string', example: 'travel'),
        new OA\Property(property: 'target_amount', type: 'number', example: 5000),
        new OA\Property(property: 'current_amount', type: 'number', example: 3200),
        new OA\Property(property: 'target_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(
            property: 'progress',
            properties: [
                new OA\Property(property: 'current', type: 'number', example: 3200),
                new OA\Property(property: 'target', type: 'number', example: 5000),
                new OA\Property(property: 'remaining', type: 'number', example: 1800),
                new OA\Property(property: 'percentage', type: 'number', example: 64),
                new OA\Property(property: 'status', type: 'string', example: 'on_track'),
            ],
            type: 'object',
        ),
        new OA\Property(
            property: 'forecast',
            properties: [
                new OA\Property(property: 'remaining', type: 'number', example: 1800),
                new OA\Property(property: 'days_remaining', type: 'integer', example: 120),
                new OA\Property(property: 'required_monthly', type: 'number', example: 450),
                new OA\Property(property: 'projected_completion', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'is_behind', type: 'boolean', example: false),
            ],
            type: 'object',
        ),
    ],
)]
class SavingsGoal {}
