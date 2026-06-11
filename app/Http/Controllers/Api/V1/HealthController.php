<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class HealthController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return $this->success(
            data: [
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
            ],
            message: 'API is healthy.',
        );
    }
}
