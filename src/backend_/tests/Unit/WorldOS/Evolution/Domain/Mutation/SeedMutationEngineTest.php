<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Evolution\Domain\Mutation;

use PHPUnit\Framework\TestCase;
use WorldOS\Evolution\Domain\Mutation\Service\SeedMutationEngine;
use WorldOS\Evolution\Domain\Seed\ValueObject\UniverseSeed;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;

final class SeedMutationEngineTest extends TestCase
{
    public function test_seed_mutation_alters_genetics_and_increments_generation(): void
    {
        $engine = new SeedMutationEngine();
        $myth = MythVector::genesis();
        $coupling = [
            'ascension' => ['stress' => 0.1, 'fear' => -0.2],
        ];

        $seed = new UniverseSeed(
            mythImprint: $myth,
            couplingMatrix: $coupling,
            spectralRadius: 0.98,
            entropyResidual: 0.5,
            generation: 3,
            parentUniverseId: 'univ_parent'
        );

        $mutated = $engine->mutate($seed);

        // Verify generation increments
        $this->assertSame(4, $mutated->generation);
        $this->assertNull($mutated->parentUniverseId);
        
        // Entropy residual is dampened (* 0.9)
        $this->assertEquals(0.45, $mutated->entropyResidual); 
        
        // Spectral radius should be mutated but within strict bounds (0.9 to 0.999)
        $this->assertGreaterThanOrEqual(0.9, $mutated->spectralRadius);
        $this->assertLessThanOrEqual(0.999, $mutated->spectralRadius);

        // Matrix is perturbed (not exactly identical)
        $this->assertNotEquals($coupling, $mutated->couplingMatrix);
    }
}
