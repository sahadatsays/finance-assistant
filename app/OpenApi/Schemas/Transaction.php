<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Transaction',
    required: ['id', 'type', 'amount', 'occurred_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 42),
        new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense', 'transfer'], example: 'expense'),
        new OA\Property(property: 'amount', type: 'number', format: 'float', example: 125.5),
        new OA\Property(property: 'notes', type: 'string', nullable: true, example: 'Weekly groceries'),
        new OA\Property(property: 'occurred_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'account',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Checking'),
            ],
            type: 'object',
        ),
        new OA\Property(
            property: 'category',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 5),
                new OA\Property(property: 'name', type: 'string', example: 'Groceries'),
                new OA\Property(property: 'color', type: 'string', example: '#f59e0b'),
            ],
            type: 'object',
            nullable: true,
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                ],
                type: 'object',
            ),
        ),
    ],
)]
class Transaction {}
