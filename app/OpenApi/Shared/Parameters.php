<?php

namespace App\OpenApi\Shared;

use OpenApi\Attributes as OA;

#[OA\Parameter(
    parameter: 'XTenantId',
    name: 'X-Tenant-Id',
    description: 'Active tenant workspace ID. Optional when the user belongs to a single tenant.',
    in: 'header',
    required: false,
    schema: new OA\Schema(type: 'integer', example: 1),
)]
#[OA\Parameter(
    parameter: 'Page',
    name: 'page',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
)]
#[OA\Parameter(
    parameter: 'PerPage',
    name: 'per_page',
    in: 'query',
    required: false,
    schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15),
)]
class Parameters {}
