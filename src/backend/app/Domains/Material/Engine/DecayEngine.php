<?php

namespace App\Domains\Material\Engine;

use App\Domains\Material\MaterialInstance;
use App\Domains\Material\Enums\MaterialLifecycle;

class DecayEngine
{
    /**
     * Process decay for a material instance.
     * Returns the updated instance state.
     */
    public function processDecay(MaterialInstance $instance): MaterialInstance
    {
        $material = $instance->material;

        // 1. Institutional materials decay rapidly if not reinforced
        if ($material->ontology === \App\Domains\Material\Enums\MaterialOntology::INSTITUTIONAL) {
             if ($instance->strength_level < 3) {
                 $instance->degradation_level += 2;
             } else {
                 $instance->degradation_level += 1;
             }
        }

        // 2. Symbolic materials decay slowly
        if ($material->ontology === \App\Domains\Material\Enums\MaterialOntology::SYMBOLIC) {
            // Only decay if very weak or specifically targeted
            if ($instance->strength_level < 1) {
                $instance->degradation_level += 1;
            }
        }

        // Check for retirement (Death)
        if ($instance->degradation_level >= 100) {
            $instance->retired_at = now();
            // Transition to Legacy lifecycle is implied by retired_at
        }

        return $instance;
    }
}
