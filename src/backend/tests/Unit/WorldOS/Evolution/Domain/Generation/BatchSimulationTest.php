<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Evolution\Domain\Generation;

use PHPUnit\Framework\TestCase;
use WorldOS\Evolution\Domain\Fitness\Service\AdaptiveFitnessController;
use WorldOS\Evolution\Domain\Mutation\Service\SeedMutationEngine;
use WorldOS\Evolution\Domain\Seed\ValueObject\UniverseSeed;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;

final class BatchSimulationTest extends TestCase
{
    public function test_batch_simulation_flow_evaluates_and_mutates(): void
    {
        // 1. Spawn initial seed
        $seed = new UniverseSeed(
            mythImprint: MythVector::genesis(),
            couplingMatrix: [],
            spectralRadius: 0.98,
            entropyResidual: 1.0,
            generation: 1,
            parentUniverseId: null
        );

        // 2. Mock evaluate fitness -> ecosystem volatility
        $controller = new AdaptiveFitnessController();
        $weights = $controller->adjustWeights(0.6); 
        
        $this->assertEquals(0.6, $weights->complexity);
        $this->assertEquals(0.2, $weights->stability);

        // 3. Mutate for next generation
        $mutationEngine = new SeedMutationEngine();
        $nextGenSeed = $mutationEngine->mutate($seed);

        // 4. Verify Generation 2 DNA
        $this->assertSame(2, $nextGenSeed->generation);
        $this->assertEquals(0.9, $nextGenSeed->entropyResidual); // Dampen 0.9x
        $this->assertGreaterThanOrEqual(0.9, $nextGenSeed->spectralRadius);
        $this->assertLessThanOrEqual(0.999, $nextGenSeed->spectralRadius);
    }
}
