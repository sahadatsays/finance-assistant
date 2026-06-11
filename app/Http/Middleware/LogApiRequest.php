<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('api.logging.enabled', true)) {
            return $next($request);
        }

        $startedAt = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        $context = [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => $request->userAgent(),
        ];

        if (config('api.logging.log_request_body', false)) {
            $context['request'] = $request->except(['password', 'password_confirmation', 'token']);
        }

        if (config('api.logging.log_response_body', false)) {
            $context['response'] = $response->getContent();
        }

        Log::channel(config('api.logging.channel', 'api'))->info('API request', $context);

        return $response;
    }
}
