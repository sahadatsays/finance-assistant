<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

abstract class ApiController extends Controller
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    protected function success(
        mixed $data = null,
        string $message = '',
        int $status = 200,
        ?array $meta = null,
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status, $meta);
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $meta
     */
    protected function error(
        string $message,
        int $status = 400,
        mixed $data = null,
        ?array $meta = null,
    ): JsonResponse {
        return ApiResponse::error($message, $status, $data, $meta);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function paginated(
        AbstractPaginator $paginator,
        string $message = '',
        array $meta = [],
    ): JsonResponse {
        return ApiResponse::paginated($paginator, $message, $meta);
    }
}
