<?php

/**
 * Saga config. WorldOS 2.0: strict_runtime = true enforces "Saga never ticks World" (only Universe).
 *
 * @see docs/WORLDOS_2_FINAL_FORM_AND_LAB.md
 */
return [
    'strict_runtime' => (bool) env('SAGA_STRICT_RUNTIME', false),

    'shock_enabled' => env('SAGA_SHOCK_ENABLED', true),
    'shock_interval_years' => (int) env('SAGA_SHOCK_INTERVAL_YEARS', 75),
    'shock_magnitude_min' => (float) env('SAGA_SHOCK_MAGNITUDE_MIN', 0.1),
    'shock_magnitude_max' => (float) env('SAGA_SHOCK_MAGNITUDE_MAX', 0.3),

    'convergence_exploration_min' => (float) env('SAGA_CONVERGENCE_EXPLORATION_MIN', 0.02),
    'convergence_decay_half_life' => (float) env('SAGA_CONVERGENCE_DECAY_HALF_LIFE', 20.0),
];
