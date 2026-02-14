<?php

namespace Tests\Unit\Domains\Evolution;

use App\Domains\Evolution\Models\EvolutionProfile;
use App\Domains\Evolution\Services\EvolutionKernel;
use App\Domains\Evolution\ValueObjects\StateVector;
use PHPUnit\Framework\TestCase;

class EvolutionKernelTest extends TestCase
{
    public function test_it_evolves_state_vector()
    {
        $kernel = new EvolutionKernel();
        
        $current = new StateVector(
            coherence: 0.5,
            entropy: 0.1,
            belief_mass: 0.1,
            resource_flow: 0.5,
            stability: 0.8,
            innovation_rate: 0.2
        );

        $profile = new EvolutionProfile([
            'coefficients' => [
                'belief_growth' => 0.1,
                'entropy_decay' => 0.05,
                'resource_consumption' => 0.05,
                'innovation_bonus' => 0.1,
                'stability_recovery' => 0.1,
            ],
            'alpha' => 1.0,
        ]);

        $next = $kernel->nextTick($current, $profile);

        // Assert basic evolution logic
        // Belief should grow: 0.1 * 0.5 * 1.0 - 0.01 = 0.04 delta -> 0.14
        $this->assertEquals(0.14, $next->belief_mass);
        
        // Entropy should adjust: 0.05 * (1 - 0.5) + 0 = 0.025 delta -> 0.125
        $this->assertEquals(0.125, $next->entropy);
        
        // Ensure clamping works
        $this->assertGreaterThanOrEqual(0.0, $next->coherence);
        $this->assertLessThanOrEqual(1.0, $next->coherence);
    }
}
