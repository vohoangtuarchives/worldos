<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Dynamics;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\VectorForce;

/**
 * DriftField - Intrinsic baseline force from state (no influence).
 * Inequality high -> cohesion down; innovation high -> legitimacy oscillate; trauma high -> elite_cohesion polarize.
 */
final class DriftField
{
    private const DT = 0.01;

    public function compute(WorldStateVector $state): VectorForce
    {
        $order = $state->getOrder();
        $entropy = $state->getEntropy();
        $cohesion = $state->getCohesion();
        $legitimacy = $state->getLegitimacy();
        $innovation = $state->getInnovation();
        $inequality = $state->getInequality();
        $trauma = $state->getTrauma();
        $eliteCohesion = $state->getEliteCohesion();
        $resourceStock = $state->getResourceStock();

        // Inequality high -> cohesion decreases
        $dCohesion = -0.02 * $inequality * (1.0 - $cohesion);

        // Innovation high -> legitimacy oscillates (slight negative pressure from disruption)
        $dLegitimacy = -0.01 * $innovation * $legitimacy + 0.005 * $order;

        // Trauma high -> elite_cohesion polarizes (decreases)
        $dEliteCohesion = -0.03 * $trauma * $eliteCohesion;

        // Order: entropy opposes order
        $dOrder = 0.01 * (1.0 - $entropy) - 0.01 * $entropy * $order;

        // Entropy: natural drift up slightly, cohesion dampens
        $dEntropy = 0.005 * (1.0 - $entropy) - 0.005 * $cohesion * $entropy;

        // Innovation: order supports, trauma dampens
        $dInnovation = 0.01 * $order - 0.01 * $trauma;

        // Military: threat from entropy and trauma
        $dMilitary = 0.005 * $entropy + 0.005 * $trauma - 0.01 * $legitimacy;

        // Resource: innovation and order support, entropy drains
        $dResourceStock = 0.005 * $innovation + 0.005 * $order - 0.01 * $entropy;

        // Inequality: rises with low legitimacy, falls with high cohesion
        $dInequality = 0.01 * (1.0 - $legitimacy) - 0.01 * $cohesion * $inequality;

        // Trauma: entropy and military pressure increase, resilience (1 - entropy) decreases
        $dTrauma = 0.005 * $entropy + 0.005 * $state->getMilitary() - 0.005 * (1.0 - $entropy);

        $components = [
            WorldStateVector::DIMENSION_ORDER => $dOrder * self::DT,
            WorldStateVector::DIMENSION_ENTROPY => $dEntropy * self::DT,
            WorldStateVector::DIMENSION_COHESION => $dCohesion * self::DT,
            WorldStateVector::DIMENSION_LEGITIMACY => $dLegitimacy * self::DT,
            WorldStateVector::DIMENSION_INNOVATION => $dInnovation * self::DT,
            WorldStateVector::DIMENSION_MILITARY => $dMilitary * self::DT,
            WorldStateVector::DIMENSION_INEQUALITY => $dInequality * self::DT,
            WorldStateVector::DIMENSION_TRAUMA => $dTrauma * self::DT,
            WorldStateVector::DIMENSION_ELITE_COHESION => $dEliteCohesion * self::DT,
            WorldStateVector::DIMENSION_RESOURCE_STOCK => $dResourceStock * self::DT,
        ];

        return new VectorForce($components);
    }
}
