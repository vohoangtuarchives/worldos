<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Services;

use WorldOS\Domains\Evolution\ValueObjects\WorldStateVector;

/**
 * Structural Mutation Engine â€” Collapse = mutation, not hard reset.
 * Power redistribution, resource shift, ideology reset (partial), cohesion rewire.
 * Intensity = P^3. Post-collapse stabilization applied after mutation.
 */
final class StructuralMutationEngine
{
    /**
     * Apply structural mutation to state when collapse occurs.
     * Returns new WorldStateVector; does not mutate in place.
     */
    public function mutate(WorldStateVector $state, float $pressure): WorldStateVector
    {
        $intensity = $pressure * $pressure * $pressure; // P^3
        $comp = $state->getAll();

        // 1. Power redistribution: reduce inequality, break elite cohesion
        $comp[WorldStateVector::DIMENSION_INEQUALITY] = max(0, $comp[WorldStateVector::DIMENSION_INEQUALITY] - 0.3 * $intensity);
        $comp[WorldStateVector::DIMENSION_ELITE_COHESION] = max(0, $comp[WorldStateVector::DIMENSION_ELITE_COHESION] - 0.4 * $intensity);

        // 2. Resource model shift: partial reset / reallocation
        $comp[WorldStateVector::DIMENSION_RESOURCE_STOCK] = min(1.0, $comp[WorldStateVector::DIMENSION_RESOURCE_STOCK] * (1 - 0.3 * $intensity) + 0.2 * $intensity);

        // 3. Ideology reset (partial): reduce divergence â†’ raise cohesion and legitimacy
        $comp[WorldStateVector::DIMENSION_COHESION] = min(1.0, $comp[WorldStateVector::DIMENSION_COHESION] + 0.2 * $intensity);
        $comp[WorldStateVector::DIMENSION_LEGITIMACY] = min(1.0, $comp[WorldStateVector::DIMENSION_LEGITIMACY] + 0.15 * $intensity);

        // 4. Social rewire: order and entropy adjusted (structure reforms)
        $comp[WorldStateVector::DIMENSION_ORDER] = min(1.0, $comp[WorldStateVector::DIMENSION_ORDER] * (1 - 0.2 * $intensity) + 0.1 * $intensity);
        $comp[WorldStateVector::DIMENSION_ENTROPY] = max(0, $comp[WorldStateVector::DIMENSION_ENTROPY] - 0.15 * $intensity);

        $next = new WorldStateVector($comp);
        return $this->applyStabilization($next);
    }

    /** PostCollapseStabilizationPolicy: reduce divergence/fragmentation and pressure. */
    private function applyStabilization(WorldStateVector $state): WorldStateVector
    {
        $comp = $state->getAll();
        $comp[WorldStateVector::DIMENSION_COHESION] = min(1.0, $comp[WorldStateVector::DIMENSION_COHESION] + 0.05);
        $comp[WorldStateVector::DIMENSION_ENTROPY] = max(0, $comp[WorldStateVector::DIMENSION_ENTROPY] - 0.03);
        $comp[WorldStateVector::DIMENSION_TRAUMA] = max(0, $comp[WorldStateVector::DIMENSION_TRAUMA] - 0.02);
        return new WorldStateVector($comp);
    }
}



