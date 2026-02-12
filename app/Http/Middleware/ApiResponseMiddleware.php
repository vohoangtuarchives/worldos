<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        // Only handle JSON responses
        if (!$response instanceof JsonResponse) {
            return $response;
        }

        // Add standard headers
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-API-Version', '1.0');
        $response->headers->set('X-Response-Time', microtime(true) - LARAVEL_START);

        // Log API calls for debugging
        if (config('app.debug')) {
            Log::info('API Response', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status' => $response->getStatusCode(),
                'response_time' => $response->headers->get('X-Response-Time'),
            ]);
        }

        // Ensure consistent response format
        $data = $response->getData(true);
        
        if (!isset($data['success'])) {
            $data = [
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ];
            $response->setData($data);
        }

        return $response;
    }
}
