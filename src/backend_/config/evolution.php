<?php

/**
 * Evolution Lab & Meta-Evaluation (WorldOS 2.0 Final Form).
 * Long-running evolutionary engine; AI is optional enrichment, toggleable at runtime.
 *
 * @see docs/WORLDOS_2_FINAL_FORM_AND_LAB.md
 */
return [
    'ai_enabled' => (bool) env('EVOLUTION_AI_ENABLED', false),
    'ai_weight' => (float) env('EVOLUTION_AI_WEIGHT', 0.2),
    'ai_sampling_rate' => (float) env('EVOLUTION_AI_SAMPLING_RATE', 1.0),
    'ai_model_version' => env('EVOLUTION_AI_MODEL_VERSION', 'gpt-4o'),
    'ai_queue' => env('EVOLUTION_AI_QUEUE', 'ai'),
    'simulation_queue' => env('EVOLUTION_SIMULATION_QUEUE', 'simulation'),
];
