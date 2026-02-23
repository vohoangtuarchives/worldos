<?php

namespace WorldOS\Legacy\Application\Cosmology\Agents;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Cosmology\Mathematics\Vector;

class PopulationBlock
{
    /**
     * The Masses generate pressure based on their suffering.
     * Unlike Agents, they don't have 'willpower' but rather 'reaction'.
     */
    public function generatePressure(WorldStateVector $state): Vector
    {
        $inequality = $state->getInequality();
        $trauma = $state->getTrauma();
        $legitimacy = $state->getLegitimacy();

        // 1. Radicalization Index
        // High Inequality + High Trauma = Extreme Radicalization
        $radicalization = ($inequality * 1.5) + ($trauma * 0.5) - ($legitimacy * 0.8);
        $radicalization = max(0.0, min(1.0, $radicalization));

        $d = [];

        // 2. Pressure on Order
        // If radicalized, Order decays faster (Civil Disobedience)
        if ($radicalization > 0.4) {
            $d[WorldStateVector::DIMENSION_ORDER] = -0.05 * $radicalization;
            $d[WorldStateVector::DIMENSION_COHESION] = -0.05 * $radicalization;
        }

        // 3. Pressure on Legitimacy
        // The masses withdraw consent
        $d[WorldStateVector::DIMENSION_LEGITIMACY] = -0.03 * $radicalization;

        // 4. Pressure on Entropy
        // Radicalization fuels chaos
        $d[WorldStateVector::DIMENSION_ENTROPY] = 0.04 * $radicalization;

        // 5. Guillotine Factor
        // If Radicalization > 0.8, Elite Cohesion is attacked directly
        if ($radicalization > 0.8) {
            $d[WorldStateVector::DIMENSION_ELITE_COHESION] = -0.1; // Purges
            $d[WorldStateVector::DIMENSION_TRAUMA] = 0.05; // Terror
        }

        return new Vector($d);
    }
}
