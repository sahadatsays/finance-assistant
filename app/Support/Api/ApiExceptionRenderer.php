<?php

namespace App\Support\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function handles(Request $request): bool
    {
        return $request->is('api/*');
    }

    public static function render(Throwable $exception, Request $request): ?JsonResponse
    {
        if (! self::handles($request)) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError(
                'The given data was invalid.',
                $exception->errors(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Unauthenticated.',
                Response::HTTP_UNAUTHORIZED,
            );
        }

        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                $exception->getMessage() ?: 'Forbidden.',
                Response::HTTP_FORBIDDEN,
            );
        }

        if ($exception instanceof ModelNotFoundException) {
            return ApiResponse::error(
                'Resource not found.',
                Response::HTTP_NOT_FOUND,
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::error(
                'Endpoint not found.',
                Response::HTTP_NOT_FOUND,
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            return ApiResponse::error(
                $exception->getMessage() ?: Response::$statusTexts[$exception->getStatusCode()] ?? 'Error',
                $exception->getStatusCode(),
            );
        }

        if (config('app.debug')) {
            return ApiResponse::error(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [
                    'exception' => $exception::class,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ],
            );
        }

        return ApiResponse::error(
            'An unexpected error occurred.',
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
