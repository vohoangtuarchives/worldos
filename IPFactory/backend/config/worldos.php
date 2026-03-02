<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulation Engine (Rust gRPC)
    |--------------------------------------------------------------------------
    */
    'simulation_engine_grpc_url' => env('SIMULATION_ENGINE_GRPC_URL', 'localhost:50051'),

    'narrative_llm_url' => env('NARRATIVE_LLM_URL', ''),

];
