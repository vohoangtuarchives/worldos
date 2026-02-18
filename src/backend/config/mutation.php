<?php

return [
    'max_delta_per_dimension' => (float) env('MUTATION_MAX_DELTA_PER_DIMENSION', 0.1),
    'max_total_magnitude' => env('MUTATION_MAX_TOTAL_MAGNITUDE') ? (float) env('MUTATION_MAX_TOTAL_MAGNITUDE') : null,
    'narrative_affects_universe' => (bool) env('MUTATION_NARRATIVE_AFFECTS_UNIVERSE', false),
    // WorldOS 2.0 Clean: when true, narrative should inject pressure signal (PhaseEngine) instead of mutating vector; not yet implemented.
    'narrative_affects_via_pressure' => (bool) env('MUTATION_NARRATIVE_VIA_PRESSURE', false),
    'narrative_max_magnitude' => (float) env('MUTATION_NARRATIVE_MAX_MAGNITUDE', 0.15),
];
