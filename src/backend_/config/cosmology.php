<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cosmology
    |--------------------------------------------------------------------------
    | Không giới hạn tuổi trong lifecycle — universe chỉ chết khi:
    | STRUCTURAL_FRACTURE, HEAT_DEATH, hoặc STAGNATION.
    */

    // Meta-history: attractor dominance inertia
    'dominance_margin' => (float) env('COSMOLOGY_DOMINANCE_MARGIN', 0.1),
    'inertia_cycles' => (int) env('COSMOLOGY_INERTIA_CYCLES', 20),

    // Centroid drift (slow, stable)
    'attractor_drift_alpha' => (float) env('COSMOLOGY_ATTRACTOR_DRIFT_ALPHA', 0.02),

    // Mutation: attractor identity change
    'mutation_distance_threshold' => (float) env('COSMOLOGY_MUTATION_DISTANCE_THRESHOLD', 0.35),
    'mutation_min_dominance_cycles' => (int) env('COSMOLOGY_MUTATION_MIN_DOMINANCE_CYCLES', 150),
    'mutation_min_pressure_peak' => (float) env('COSMOLOGY_MUTATION_MIN_PRESSURE_PEAK', 0.85),
];
