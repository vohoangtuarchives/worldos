<?php

namespace Tests\Unit\Cosmology;

use Tuzy\Domain\Cosmology\Cosmology;
use Tuzy\Application\Cosmology\Entities\Universe;
use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Application\Cosmology\Services\BasePhysicsEngine;
use PHPUnit\Framework\TestCase;

class CosmologyTest extends TestCase
{
    public function test_simulation_tick_evolves_universes()
    {
        // Setup
        $cosmology = Cosmology::boot(); // Uses default BasePhysicsEngine
        
        // Create a universe
        $initialState = WorldStateVector::create(0.9, 0.1, 0.9, 0.9, 0.5, 0.5);
        $universe = new Universe($initialState, [], 'uni-1');
        
        $cosmology->getFieldSpace()->addUniverse($universe);

        // Act
        $cosmology->tick();

        // Assert
        $evolvedState = $universe->getState();
        $this->assertEquals(1, $universe->getAge());
        
        // Expect entropy to change slightly (Evolution logic)
        // Similar to Kernel test, but verifying integration
        $this->assertNotEquals(0.1, $evolvedState->getEntropy());
    }

    public function test_multiple_universes_evolution()
    {
        $cosmology = Cosmology::boot();
        
        $u1 = new Universe(WorldStateVector::create(0.9, 0.1, 0.9, 0.9, 0.5, 0.5), [], 'u1');
        $u2 = new Universe(WorldStateVector::create(0.2, 0.8, 0.2, 0.2, 0.5, 0.5), [], 'u2'); // Chaos universe

        $cosmology->getFieldSpace()->addUniverse($u1);
        $cosmology->getFieldSpace()->addUniverse($u2);

        $cosmology->tick();

        $this->assertEquals(1, $u1->getAge());
        $this->assertEquals(1, $u2->getAge());

        // Verify divergence
        // u1 should tackle towards stability (high order)
        // u2 should collapse or change drastically
        $this->assertGreaterThan($u2->getState()->getOrder(), $u1->getState()->getOrder());
    }
}
