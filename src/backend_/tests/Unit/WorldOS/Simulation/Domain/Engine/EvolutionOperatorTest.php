<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Simulation\Domain\Engine;

use PHPUnit\Framework\TestCase;
use WorldOS\Kernel\Domain\Policy\CompiledPolicy;
use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;
use WorldOS\Simulation\Domain\Engine\Service\EvolutionOperator;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class EvolutionOperatorTest extends TestCase
{
    private function makeOperator(float $spectralRadius = 0.98, float $chaosFactor = 0.0): EvolutionOperator
    {
        $policy = CompiledPolicy::baseline(
            chaosFactor:    $chaosFactor,
            spectralRadius: $spectralRadius
        );

        return new EvolutionOperator(
            policy:         $policy,
            couplingMatrix: CouplingMatrix::identity($spectralRadius),
        );
    }

    // ------------------------------------------------------------------
    // Determinism
    // ------------------------------------------------------------------

    public function test_same_seed_same_tick_produces_identical_result(): void
    {
        $operator = $this->makeOperator(chaosFactor: 0.02);
        $state    = StateVector::genesis();

        $result1 = $operator->evolve($state, tick: 1, seed: 12345);
        $result2 = $operator->evolve($state, tick: 1, seed: 12345);

        foreach ($result1->nextStateVector->all() as $key => $value) {
            $this->assertEqualsWithDelta(
                $value,
                $result2->nextStateVector->get($key),
                1e-12,
                "Dimension {$key} must be deterministic"
            );
        }
    }

    public function test_different_seeds_produce_different_results(): void
    {
        $operator = $this->makeOperator(chaosFactor: 0.05);
        $state    = StateVector::genesis();

        $r1 = $operator->evolve($state, tick: 1, seed: 111);
        $r2 = $operator->evolve($state, tick: 1, seed: 999);

        // At least one dimension must differ
        $anyDiffers = false;
        foreach ($r1->nextStateVector->all() as $key => $v1) {
            if (abs($v1 - $r2->nextStateVector->get($key)) > 1e-10) {
                $anyDiffers = true;
                break;
            }
        }

        $this->assertTrue($anyDiffers, 'Different seeds should produce different state vectors');
    }

    // ------------------------------------------------------------------
    // Value bounds
    // ------------------------------------------------------------------

    public function test_output_dimensions_stay_within_unit_interval(): void
    {
        $operator = $this->makeOperator(chaosFactor: 0.5); // aggressive noise
        $state    = StateVector::genesis();

        for ($tick = 1; $tick <= 20; $tick++) {
            $result = $operator->evolve($state, tick: $tick, seed: $tick * 42);
            foreach ($result->nextStateVector->all() as $key => $value) {
                $this->assertGreaterThanOrEqual(0.0, $value, "{$key} must be >= 0");
                $this->assertLessThanOrEqual(1.0, $value, "{$key} must be <= 1");
            }
            $state = $result->nextStateVector;
        }
    }

    // ------------------------------------------------------------------
    // Convergence with spectral radius < 1
    // ------------------------------------------------------------------

    public function test_zero_chaos_converges_to_zero_over_many_ticks(): void
    {
        // With no noise and spectral radius = 0.5, all dimensions → 0
        $operator = $this->makeOperator(spectralRadius: 0.5, chaosFactor: 0.0);
        $state    = StateVector::genesis();

        for ($tick = 1; $tick <= 50; $tick++) {
            $result = $operator->evolve($state, tick: $tick, seed: 0);
            $state  = $result->nextStateVector;
        }

        // After 50 ticks with 0.5^50 ≈ 0 scaling, all dimensions must be near 0
        foreach ($state->all() as $key => $value) {
            $this->assertLessThan(0.001, $value, "{$key} should converge near zero");
        }
    }

    // ------------------------------------------------------------------
    // Anomaly detection
    // ------------------------------------------------------------------

    public function test_anomaly_detected_when_dimension_exceeds_threshold(): void
    {
        $operator = $this->makeOperator(spectralRadius: 1.0, chaosFactor: 0.0);

        // Force high entropy by starting with entropy = 1.0
        $state = StateVector::fromArray(
            array_merge(StateVector::DEFAULT_DIMENSIONS, [StateVector::DIMENSION_ENTROPY => 1.0])
        );

        $result = $operator->evolve(
            current:             $state,
            tick:                1,
            seed:                0,
            criticalThresholds:  [StateVector::DIMENSION_ENTROPY => 0.5]
        );

        $this->assertNotEmpty($result->anomalies, 'Anomaly should be detected when entropy > 0.5');
    }
}
