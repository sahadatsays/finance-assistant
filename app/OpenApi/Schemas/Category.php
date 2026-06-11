<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Category',
    required: ['id', 'name', 'type', 'color'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 5),
        new OA\Property(property: 'name', type: 'string', example: 'Groceries'),
        new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense'], example: 'expense'),
        new OA\Property(property: 'color', type: 'string', example: '#f59e0b'),
        new OA\Property(property: 'icon', type: 'string', nullable: true, example: 'shopping-cart'),
        new OA\Property(property: 'kind', type: 'string', enum: ['system', 'custom'], example: 'custom'),
        new OA\Property(property: 'is_system', type: 'boolean', example: false),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'archived_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'transactions_count', type: 'integer', example: 12),
    ],
)]
#[OA\Schema(
    schema: 'StoreCategoryRequest',
    required: ['name', 'type'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 128, example: 'Side Hustle'),
        new OA\Property(property: 'type', type: 'string', enum: ['income', 'expense'], example: 'income'),
        new OA\Property(property: 'color', type: 'string', pattern: '^#[0-9A-Fa-f]{6}$', example: '#3b82f6'),
        new OA\Property(property: 'icon', type: 'string', maxLength: 64, nullable: true),
    ],
)]
class Category {}
