<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        //
    })->create();
