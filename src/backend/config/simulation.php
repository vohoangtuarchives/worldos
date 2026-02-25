<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simulation Engine gRPC Host
    |--------------------------------------------------------------------------
    | Address of the Rust Simulation Engine gRPC server.
    | In Docker: use the service name as hostname.
    | In production: set via SIMULATION_ENGINE_GRPC_HOST env variable.
    */
    'engine_grpc_host' => env('SIMULATION_ENGINE_GRPC_HOST', 'simulation-engine:50051'),
];
