<?php

namespace Tests\Unit\Cosmology;

use WorldOS\Legacy\Application\Cosmology\Agents\InfluenceField;
use WorldOS\Legacy\Application\Cosmology\Agents\TranscendentAgent;
use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use WorldOS\Legacy\Application\Cosmology\Mathematics\Vector;
use PHPUnit\Framework\TestCase;

class TranscendentAgencyTest extends TestCase
{
    public function test_agent_influence_modifies_universe_state()
    {
        // 1. Setup Universe
        $initialState = WorldStateVector::create(0.5, 0.5, 0.5, 0.5, 0.5, 0.5);
        $universe = new Universe($initialState, [], 'u-test');

        // 2. Setup Agent (Rebel - increases Entropy by 0.1)
        $influenceVector = new Vector([
            WorldStateVector::DIMENSION_ENTROPY => 0.1,
            WorldStateVector::DIMENSION_ORDER => -0.05
        ]);
        $field = new InfluenceField($influenceVector);
        $agent = new TranscendentAgent('Chaos Bringer', 'rebel', $field);

        // 3. Exert Influence
        // Use Universe method directly as Agent::exertInfluence iterates array
        $universe->applyAgentInfluence($agent);

        // 4. Assert
        $newState = $universe->getState();
        $this->assertEqualsWithDelta(0.6, $newState->getEntropy(), 0.0001);
        $this->assertEqualsWithDelta(0.45, $newState->getOrder(), 0.0001);
        $this->assertEqualsWithDelta(0.5, $newState->getCohesion(), 0.0001); // Unchanged
    }
}
