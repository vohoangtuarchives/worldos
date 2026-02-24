<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Simulation;

use PHPUnit\Framework\TestCase;
use WorldOS\Kernel\Domain\Policy\CompiledPolicy;
use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;
use WorldOS\Simulation\Domain\Engine\Service\EvolutionOperator;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

class EvolutionOperatorTest extends TestCase
{
    private EvolutionOperator $operator;

    protected function setUp(): void
    {
        $policy = CompiledPolicy::baseline(chaosFactor: 0.02, spectralRadius: 0.98);

        $this->operator = new EvolutionOperator(
            policy:         $policy,
            couplingMatrix: CouplingMatrix::identity(0.98),
        );
    }

    public function test_evolution_is_deterministic_given_same_seed()
    {
        $state = StateVector::genesis();

        $result1 = $this->operator->evolve($state, tick: 1, seed: 42);
        $result2 = $this->operator->evolve($state, tick: 1, seed: 42);

        // Identical seed + identical tick → identical output
        $this->assertEquals(
            $result1->nextStateVector->toArray(),
            $result2->nextStateVector->toArray()
        );
        $this->assertEquals($result1->existenceWeight, $result2->existenceWeight);
    }

    public function test_different_seeds_produce_different_states()
    {
        $state = StateVector::genesis();

        $result1 = $this->operator->evolve($state, tick: 1, seed: 42);
        $result2 = $this->operator->evolve($state, tick: 1, seed: 99);

        // Different seeds should produce some variation in at least one dimension
        $this->assertNotEquals(
            $result1->nextStateVector->toArray(),
            $result2->nextStateVector->toArray()
        );
    }

    public function test_all_state_dimensions_remain_within_bounds()
    {
        $state = StateVector::genesis();
        $result = $this->operator->evolve($state, tick: 1, seed: 1234);

        foreach ($result->nextStateVector->all() as $key => $value) {
            $this->assertGreaterThanOrEqual(0.0, $value, "Dimension [{$key}] went below 0.0");
            $this->assertLessThanOrEqual(1.0, $value, "Dimension [{$key}] exceeded 1.0");
        }
    }

    public function test_tick_result_detects_critical_threshold_anomaly()
    {
        // Inject a state with a very high cosmic_tension
        $state = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.95);

        $thresholds = [StateVector::DIMENSION_COSMIC_TENSION => 0.80];
        $result = $this->operator->evolve($state, tick: 1, seed: 999, criticalThresholds: $thresholds);

        $this->assertTrue($result->hasAnomalies(), "Should detect a cosmic_tension anomaly above 0.80");
    }
}
