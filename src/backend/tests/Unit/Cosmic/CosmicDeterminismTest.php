<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Services\WaveInterferenceEngine;
use App\Domains\Cosmic\Services\CosmicEvolutionService;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use Tuzy\Domain\Cosmic\ValueObject\Attractor;
use PHPUnit\Framework\TestCase;

class CosmicDeterminismTest extends TestCase
{
    private WaveInterferenceEngine $waveEngine;
    private CosmicEvolutionService $evolutionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->waveEngine = new WaveInterferenceEngine();
        $this->evolutionService = new CosmicEvolutionService($this->waveEngine);
    }

    /**
     * CORE TEST: Same input → same output. Always.
     */
    public function test_wave_engine_is_deterministic(): void
    {
        $year = 12345;

        $energy1 = $this->waveEngine->energyAt($year);
        $energy2 = $this->waveEngine->energyAt($year);

        $this->assertSame($energy1, $energy2, 'Wave engine must return identical values for same year');
    }

    /**
     * Energy must always be bounded between 0 and 1.
     */
    public function test_wave_energy_is_bounded(): void
    {
        for ($year = -50000; $year <= 50000; $year += 500) {
            $energy = $this->waveEngine->energyAt($year);
            $this->assertGreaterThanOrEqual(0.0, $energy, "Energy at year $year must be >= 0");
            $this->assertLessThanOrEqual(1.0, $energy, "Energy at year $year must be <= 1");
        }
    }

    /**
     * The interference pattern must be quasi-periodic: NOT constant, but bounded.
     */
    public function test_wave_is_not_constant(): void
    {
        $values = [];
        for ($year = 0; $year < 10000; $year += 100) {
            $values[] = $this->waveEngine->energyAt($year);
        }

        $unique = array_unique(array_map(fn($v) => round($v, 8), $values));
        $this->assertGreaterThan(1, count($unique), 'Wave pattern should not be a flat line');
    }

    /**
     * CORE TEST: Full state evolution is deterministic.
     * Running the same simulation twice must yield identical trajectories.
     */
    public function test_cosmic_evolution_is_deterministic(): void
    {
        $initial = CosmicState::defaultObservation(0);

        $trajectory1 = $this->evolutionService->simulate($initial, 100);
        $trajectory2 = $this->evolutionService->simulate($initial, 100);

        $this->assertCount(101, $trajectory1);
        $this->assertCount(101, $trajectory2);

        for ($i = 0; $i < 101; $i++) {
            $this->assertEquals(
                $trajectory1[$i]->toArray(),
                $trajectory2[$i]->toArray(),
                "State at step $i must be identical across runs"
            );
        }
    }

    /**
     * Entropy must stay bounded even over very long runs.
     */
    public function test_entropy_stays_bounded_over_long_run(): void
    {
        $state = CosmicState::defaultObservation(0);

        // 10,000 steps × 100 years = 1,000,000 years
        for ($i = 0; $i < 10000; $i++) {
            $state = $this->evolutionService->step($state);
        }

        $this->assertGreaterThanOrEqual(0.0, $state->entropy, 'Entropy must not go negative');
        $this->assertLessThanOrEqual(1.0, $state->entropy, 'Entropy must not exceed 1.0');
    }

    /**
     * Strain must trigger fracture events, not grow unbounded.
     */
    public function test_strain_triggers_fracture(): void
    {
        // Start with high entropy to accelerate strain buildup
        $state = new CosmicState(
            entropy: 0.80,
            energy: 0.30,
            causality: 0.70,
            strain: 0.85,
            stability: 0.20,
            currentAttractor: 'HIGH_CHAOS',
            year: 5000,
        );

        $foundFracture = false;
        $previousStrain = $state->strain;

        for ($i = 0; $i < 100; $i++) {
            $state = $this->evolutionService->step($state);
            if ($state->strain < $previousStrain * 0.5) {
                $foundFracture = true;
                break;
            }
            $previousStrain = $state->strain;
        }

        $this->assertTrue($foundFracture, 'Fracture (strain release) must occur under high strain conditions');
    }

    /**
     * Attractor catalog must have at least 4 regimes.
     */
    public function test_attractor_catalog_has_four_regimes(): void
    {
        $catalog = Attractor::catalog();

        $this->assertCount(4, $catalog);
        $this->assertArrayHasKey('EQUILIBRIUM', $catalog);
        $this->assertArrayHasKey('HIGH_CHAOS', $catalog);
        $this->assertArrayHasKey('RESONANCE_DOMINANT', $catalog);
        $this->assertArrayHasKey('VOID_COLLAPSE', $catalog);
    }

    /**
     * Every attractor must point to at least one valid transition.
     */
    public function test_all_attractors_have_valid_transitions(): void
    {
        $catalog = Attractor::catalog();

        foreach ($catalog as $attractor) {
            $this->assertNotEmpty($attractor->transitionsTo, "{$attractor->code} must have at least one transition");

            foreach ($attractor->transitionsTo as $targetCode) {
                $this->assertArrayHasKey(
                    $targetCode,
                    $catalog,
                    "{$attractor->code} transitions to non-existent attractor {$targetCode}"
                );
            }
        }
    }

    /**
     * CosmicState serialization round-trip must be lossless.
     */
    public function test_cosmic_state_serialization_roundtrip(): void
    {
        $state = CosmicState::defaultObservation(42);
        $array = $state->toArray();
        $restored = CosmicState::fromArray($array);

        $this->assertEquals($state->toArray(), $restored->toArray());
    }

    /**
     * Negative years must work (infinite cycle, no beginning).
     */
    public function test_negative_years_work(): void
    {
        $energy = $this->waveEngine->energyAt(-10000);
        $this->assertGreaterThanOrEqual(0.0, $energy);
        $this->assertLessThanOrEqual(1.0, $energy);

        $state = CosmicState::defaultObservation(-5000);
        $next = $this->evolutionService->step($state);
        $this->assertEquals(-4900, $next->year);
    }
}
