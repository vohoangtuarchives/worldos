<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Universe Evaluator Driver
    |--------------------------------------------------------------------------
    | Determines which evaluator to use for universe IP scoring and decisions.
    | Supported: "stub" (heuristic-only), "llm" (LLM-powered with stub fallback)
    */
    'evaluator_driver' => env('WORLDOS_EVALUATOR_DRIVER', 'stub'),

];
