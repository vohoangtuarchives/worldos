<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Service;

use WorldOS\Evolution\Domain\Legacy\ValueObject\CivilizationSnapshot;
use WorldOS\Evolution\Domain\Legacy\Entity\WorldState;

/**
 * InternalPressureCalculator
 * 
 * Re-implementation of V3 pressure mechanics.
 * Calculates various social and metaphysical tensions that drive event generation and fractures.
 */
class InternalPressureCalculator
{
    /**
     * @return array<string, float>
     */
    public function calculatePressure(CivilizationSnapshot $civ, WorldState $world): array
    {
        $pressures = [];
        $residual = $civ->getResidual();

        // 1. Social Instability: Inequality + War Trauma + World Contradiction
        $social = ($civ->inequality * 0.35)
                + ($residual->warTrauma * 0.35)
                + ($world->getContradictionIndex() * 0.30);
        
        $pressures['social_instability'] = max(0.0, min(1.0, $social));

        // 2. Metaphysical Tension: Global Entropy + Metaphysical Scars - World Coherence
        $meta = ($world->getEntropy() * 0.40)
              + ($residual->metaphysicalScar * 0.30)
              + ((1.0 - $world->getCoherence()) * 0.30);
        
        $pressures['metaphysical_tension'] = max(0.0, min(1.0, $meta));

        // 3. Ideological Schism: Elite fragmentation + Low stability + Social unrest
        $ideological = ((1.0 - $civ->eliteCohesion) * 0.35)
                     + ((1.0 - $world->getCosmicState()->stability) * 0.30)
                     + ($residual->socialUnrest * 0.35);
        
        $pressures['ideological_schism'] = max(0.0, min(1.0, $ideological));

        return $pressures;
    }
}
