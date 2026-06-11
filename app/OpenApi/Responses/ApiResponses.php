<?php

namespace App\OpenApi\Responses;

use OpenApi\Attributes as OA;

#[OA\Response(
    response: 'Success',
    description: 'Successful response',
    content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
)]
#[OA\Response(
    response: 'ValidationError',
    description: 'Validation failed',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorEnvelope',
        example: [
            'success' => false,
            'message' => 'The given data was invalid.',
            'data' => ['errors' => ['email' => ['The email field is required.']]],
            'meta' => [],
        ],
    ),
)]
#[OA\Response(
    response: 'Unauthorized',
    description: 'Authentication required or token invalid',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorEnvelope',
        example: [
            'success' => false,
            'message' => 'Unauthenticated.',
            'data' => [],
            'meta' => [],
        ],
    ),
)]
#[OA\Response(
    response: 'Forbidden',
    description: 'Authenticated but not authorized',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorEnvelope',
        example: [
            'success' => false,
            'message' => 'This action is unauthorized.',
            'data' => [],
            'meta' => [],
        ],
    ),
)]
#[OA\Response(
    response: 'NotFound',
    description: 'Resource not found',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorEnvelope',
        example: [
            'success' => false,
            'message' => 'Resource not found.',
            'data' => [],
            'meta' => [],
        ],
    ),
)]
#[OA\Response(
    response: 'ServerError',
    description: 'Unexpected server error',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorEnvelope',
        example: [
            'success' => false,
            'message' => 'Server Error',
            'data' => [],
            'meta' => [],
        ],
    ),
)]
class ApiResponses {}
