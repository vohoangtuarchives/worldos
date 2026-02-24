<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Not used in token-based auth, but kept for reference if SPA session
    | mode is ever re-enabled.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:3000,::1')),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Token expiration is validated in two places:
    |
    | 1. Per-token: createToken(..., $expiresAt) stores expires_at in DB.
    |    The guard rejects the token if expires_at is in the past.
    |
    | 2. Global (this value): Guard also rejects tokens whose created_at is
    |    older than (now - expiration minutes). Set to null to disable this
    |    check (not recommended); otherwise use minutes, e.g. 10080 = 7 days.
    |
    */

    'expiration' => value(function () {
        $v = env('SANCTUM_TOKEN_EXPIRATION');
        if ($v === null || $v === '') {
            return 60 * 24 * 7; // default 7 days
        }
        return (int) $v;
    }),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
