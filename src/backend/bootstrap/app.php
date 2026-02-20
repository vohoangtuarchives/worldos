<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api_vietnamese.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (Docker / Next.js rewrite proxy)
        $middleware->trustProxies(at: '*');

        // Token-based auth via Sanctum — no session/CSRF middleware needed.
        // Every request authenticates via Authorization: Bearer {token}.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tuzy domain exceptions → HTTP status
        $exceptions->renderable(function (Tuzy\Domain\World\Exception\WorldNotFoundException $e, Request $request): ?Response {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'error' => 'world_not_found'], 404);
            }
            return new Response($e->getMessage(), 404, ['Content-Type' => 'text/plain']);
        });
        $exceptions->renderable(function (Tuzy\Domain\Runtime\Exception\UniverseNotFoundException $e, Request $request): ?Response {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'error' => 'universe_not_found'], 404);
            }
            return new Response($e->getMessage(), 404, ['Content-Type' => 'text/plain']);
        });

        // Ensure API error responses (e.g. 500) include CORS headers so the browser doesn't report "blocked by CORS".
        $exceptions->renderable(function (\Throwable $e, Request $request): ?Response {
            if (! str_starts_with($request->path(), 'api/')) {
                return null;
            }
            $origin = $request->header('Origin');
            if (! $origin) {
                return null;
            }
            $allowed = config('cors.allowed_origins', []);
            $patterns = config('cors.allowed_origins_patterns', []);
            $allowOrigin = null;
            if ($origin && in_array($origin, $allowed, true)) {
                $allowOrigin = $origin;
            }
            if ($allowOrigin === null && $origin && ! empty($patterns)) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $origin)) {
                        $allowOrigin = $origin;
                        break;
                    }
                }
            }
            if ($allowOrigin === null) {
                return null;
            }

            // Ensure the error is logged even if we render a custom response.
            // This captures fatal errors that monolog might miss in certain Octane/Docker setups.
            \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'path' => $request->path(),
            ]);

            $status = 500;
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $status = $e->getStatusCode();
            }
            $response = $request->expectsJson()
                ? response()->json(['message' => $e->getMessage(), 'exception' => get_debug_type($e)], $status)
                : new Response($e->getMessage(), $status, ['Content-Type' => 'text/plain']);
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Expose-Headers', '');
            return $response;
        });
    })->create();
