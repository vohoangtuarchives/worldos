<?php

namespace WorldOS\Legacy\Application\Material\Engine;

use WorldOS\Legacy\Domain\Material\MaterialInstance;

class BehaviorResolver
{
    /**
     * Resolve the simulation behavior for a given material instance.
     * This maps the ontology and function to specific simulation effects.
     */
    public function resolve(MaterialInstance $instance): array
    {
        $material = $instance->material;
        
        $effects = [];

        // 1. Symbolic + Legitimizing -> Boost cohesion
        if ($material->ontology === \WorldOS\Legacy\Domain\Material\Enums\MaterialOntology::SYMBOLIC &&
            $material->function === \WorldOS\Legacy\Domain\Material\Enums\MaterialFunction::LEGITIMIZING) {
            $effects['cohesion_modifier'] = 0.1 * $instance->strength_level;
        }

        // 2. Institutional + Stabilizing -> Reduce entropy (but needs maintenance)
        if ($material->ontology === \App\Domains\Material\Enums\MaterialOntology::INSTITUTIONAL &&
            $material->function === \App\Domains\Material\Enums\MaterialFunction::STABILIZING) {
            $effects['entropy_modifier'] = -0.05 * $instance->strength_level;
            $effects['maintenance_cost'] = 10 * $instance->strength_level;
        }

        // 3. Behavioral + Destructive -> Increase fracture risk
        if ($material->ontology === \WorldOS\Legacy\Domain\Material\Enums\MaterialOntology::BEHAVIORAL &&
            $material->function === \WorldOS\Legacy\Domain\Material\Enums\MaterialFunction::DESTRUCTIVE) {
            $effects['fracture_risk'] = 0.2 * $instance->strength_level;
        }

        return $effects;
    }
}
