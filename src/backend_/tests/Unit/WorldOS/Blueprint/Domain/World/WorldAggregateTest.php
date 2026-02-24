<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Blueprint\Domain\World;

use PHPUnit\Framework\TestCase;
use WorldOS\Blueprint\Domain\World\Entity\World;
use WorldOS\Blueprint\Domain\World\ValueObject\PhysicsCore;
use WorldOS\Blueprint\Domain\World\ValueObject\NarrativeTopology;
use WorldOS\Blueprint\Domain\World\ValueObject\GeneVector;
use WorldOS\Blueprint\Domain\World\ValueObject\WorldSignature;

class WorldAggregateTest extends TestCase
{
    public function test_it_creates_deterministic_world_signatures()
    {
        $geneVector1 = GeneVector::create([], ['vitality' => [1, 10], 'magic' => [0, 5]]);
        $geneVector2 = GeneVector::create([], ['vitality' => [1, 10], 'magic' => [0, 5]]);
        
        // Two identical PhysicsCores
        $physics1 = PhysicsCore::create(17, $geneVector1, 0.98, 0.02);
        $physics2 = PhysicsCore::create(17, $geneVector2, 0.98, 0.02);

        // Two identical NarrativeTopologies
        $narrative1 = NarrativeTopology::create(0.5, 0.1, 0.2);
        $narrative2 = NarrativeTopology::create(0.5, 0.1, 0.2);

        $world1 = World::create('Genesis 1', $physics1, $narrative1, 'multi-1');
        $world2 = World::create('Genesis 2', $physics2, $narrative2, 'multi-2');

        // Signatures MUST match perfectly despite being two different world instances
        $this->assertEquals($world1->getSignature()->getHash(), $world2->getSignature()->getHash());
    }

    public function test_signature_changes_when_physics_mutates()
    {
        $geneVector = GeneVector::create([], ['vitality' => [1, 10], 'magic' => [0, 5]]);
        
        $physics1 = PhysicsCore::create(17, $geneVector, 0.98, 0.02);
        $physics2 = PhysicsCore::create(17, $geneVector, 0.95, 0.02); // Different spectral radius

        $narrative = NarrativeTopology::create(0.5, 0.1, 0.2);

        $world1 = World::create('Genesis 1', $physics1, $narrative, 'multi-1');
        $world2 = World::create('Genesis 2', $physics2, $narrative, 'multi-1');

        // Signatures MUST NOT match
        $this->assertNotEquals($world1->getSignature()->getHash(), $world2->getSignature()->getHash());
    }

    public function test_signature_preserves_independent_hashes()
    {
        $geneVector = GeneVector::create([], ['vitality' => [1, 10]]);
        $physics = PhysicsCore::create(10, $geneVector);
        $narrative = NarrativeTopology::create(0.5, 0.1, 0.2);

        $world = World::create('Test World', $physics, $narrative, 'multi-1');
        
        $sig = $world->getSignature();
        
        $this->assertNotEmpty($sig->getPhysicsHash());
        $this->assertNotEmpty($sig->getNarrativeHash());
        $this->assertNotEmpty($sig->getHash());
        
        // Final hash must be deterministic composition of the two
        $expectedHash = hash('sha256', $sig->getPhysicsHash() . $sig->getNarrativeHash());
        $this->assertEquals($expectedHash, $sig->getHash());
    }
}
