<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ApiEnvelope',
    required: ['success', 'message', 'data', 'meta'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Request completed successfully.'),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'meta', type: 'object'),
    ],
)]
#[OA\Schema(
    schema: 'ErrorEnvelope',
    required: ['success', 'message', 'data', 'meta'],
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Something went wrong.'),
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(
                    property: 'errors',
                    type: 'object',
                    additionalProperties: new OA\AdditionalProperties(
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                    ),
                    example: ['email' => ['The email field is required.']],
                ),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'meta', type: 'object'),
    ],
)]
class Envelope {}
