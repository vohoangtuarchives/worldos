<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Universe Evaluator Driver
    |--------------------------------------------------------------------------
    | Determines which evaluator to use for universe IP scoring and decisions.
    | Supported: "stub" (heuristic-only), "llm" (LLM-powered with stub fallback)
    */
    /*
    |--------------------------------------------------------------------------
    | Dynamical System Global Gain
    |--------------------------------------------------------------------------
    | Scales the intensity of the multiplicative feedback equations.
    | Higher = more catastrophic and dramatic trajectories.
    */
    'interaction_gain' => env('WORLDOS_INTERACTION_GAIN', 1.8),

];
