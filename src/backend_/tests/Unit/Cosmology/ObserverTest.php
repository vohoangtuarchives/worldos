<?php

namespace Tests\Unit\Cosmology;

use WorldOS\Legacy\Application\Cosmology\Agents\Observer;
use WorldOS\Legacy\Application\Cosmology\Entities\Universe;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;
use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{
    public function test_observation_collapses_entropy()
    {
        // High Entropy
        $initialState = WorldStateVector::create(0.1, 0.9, 0.1, 0.1, 0.1, 0.1);
        $universe = new Universe($initialState, [], 'obs-uni');
        
        $observer = new Observer('Watcher');
        
        // Observe
        $newState = $observer->observe($universe);
        
        // Entropy should decrease
        $this->assertLessThan(0.9, $newState->getEntropy());
        
        // Order should increase slightly
        $this->assertGreaterThan(0.1, $newState->getOrder());
    }
}
