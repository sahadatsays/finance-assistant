<?php

namespace App\Support\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        int $status = 200,
        ?array $meta = null,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::transformData($data),
            'meta' => $meta ?? (object) [],
        ], $status);
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @param  array<string, mixed>|null  $meta
     */
    public static function error(
        string $message,
        int $status = 400,
        mixed $data = null,
        ?array $meta = null,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data ?? (object) [],
            'meta' => $meta ?? (object) [],
        ], $status);
    }

    /**
     * @param  array<string, mixed>  $errors
     */
    public static function validationError(
        string $message,
        array $errors,
        int $status = 422,
    ): JsonResponse {
        return self::error($message, $status, ['errors' => $errors]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function paginated(
        AbstractPaginator $paginator,
        string $message = '',
        array $meta = [],
    ): JsonResponse {
        return self::success(
            $paginator->items(),
            $message,
            200,
            array_merge([
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ], $meta),
        );
    }

    private static function transformData(mixed $data): mixed
    {
        if ($data === null) {
            return (object) [];
        }

        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->resolve();
        }

        return $data;
    }
}
