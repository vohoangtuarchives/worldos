<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WorldOS Simulation Engine (Rust gRPC Server)
    |--------------------------------------------------------------------------
    | Trong Docker: dùng service name "simulation-engine:50051" (cùng app_network).
    | Trong local (non-Docker): dùng "127.0.0.1:50051".
    | Đặt biến SIMULATION_ENGINE_GRPC_HOST trong .env để ghi đè.
    */
    'simulation_engine' => [
        'host'    => env('SIMULATION_ENGINE_GRPC_HOST', 'simulation-engine:50051'),
        'timeout' => env('SIMULATION_ENGINE_TIMEOUT_MS', 5000),
    ],

];
