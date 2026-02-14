<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Services\WaveInterferenceEngine;
use App\Domains\Cosmic\Services\CosmicEvolutionService;
use App\Domains\Cosmic\Services\WorldEvolutionPipeline;
use App\Domains\Cosmic\Services\BifurcationManager;
use App\Domains\Cosmic\Services\CosmicNarrativeRenderer;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use PHPUnit\Framework\TestCase;

class WorldEvolutionPipelineTest extends TestCase
{
    private WorldEvolutionPipeline $pipeline;
    private BifurcationManager $bifurcationManager;

    protected function setUp(): void
    {
        parent::setUp();
        $waveEngine = new WaveInterferenceEngine();
        $cosmicService = new CosmicEvolutionService($waveEngine);
        $this->bifurcationManager = new BifurcationManager();
        $this->pipeline = new WorldEvolutionPipeline($cosmicService, $this->bifurcationManager);
    }

    /**
     * CORE: Full pipeline must be deterministic.
     */
    public function test_full_pipeline_is_deterministic(): void
    {
        $initial = WorldSnapshot::defaultObservation(0);

        $result1 = $this->pipeline->simulate($initial, 50);
        $result2 = $this->pipeline->simulate($initial, 50);

        $traj1 = $result1['trajectory'];
        $traj2 = $result2['trajectory'];

        for ($i = 0; $i < 51; $i++) {
            $this->assertEquals(
                $traj1[$i]->toArray(),
                $traj2[$i]->toArray(),
                "Full pipeline state at step $i must be identical"
            );
        }
    }

    /**
     * Environment must respond to cosmic pressure but with inertia (not instant).
     */
    public function test_environment_has_inertia(): void
    {
        $initial = WorldSnapshot::defaultObservation(0);
        $snapshot = $this->pipeline->step($initial);

        $this->assertNotEquals(
            $snapshot->cosmic->energy,
            $snapshot->environment->leyEnergy,
            'Environment should lag behind cosmic energy due to inertia'
        );
    }

    /**
     * Civilization knowledge must grow over time in stable conditions.
     */
    public function test_civilization_grows_in_stable_conditions(): void
    {
        $snapshot = WorldSnapshot::defaultObservation(0);

        for ($i = 0; $i < 10; $i++) {
            $snapshot = $this->pipeline->step($snapshot);
        }

        $this->assertGreaterThan(
            0.10,
            $snapshot->civilization->collectiveKnowledge,
            'Knowledge should grow from default 0.10 over 1000 years'
        );
    }

    /**
     * Resonance must build when ritual coherence and cosmic tension align.
     */
    public function test_resonance_builds_under_aligned_conditions(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.50,
                energy: 0.70,
                causality: 0.60,
                strain: 0.40,
                stability: 0.50,
                currentAttractor: 'EQUILIBRIUM',
                year: 0
            ),
            environment: EnvironmentState::defaultObservation(0),
            civilization: new CivilizationState(
                collectiveKnowledge: 0.80,
                ritualCoherence: 0.90,
                technologicalLevel: 0.30,
                factionStability: 0.70,
                resonanceAccumulator: 0.0,
                year: 0
            ),
            year: 0,
        );

        for ($i = 0; $i < 50; $i++) {
            $snapshot = $this->pipeline->step($snapshot);
        }

        $this->assertGreaterThan(
            0.0,
            $snapshot->civilization->resonanceAccumulator,
            'Resonance should accumulate with high ritual coherence and cosmic activity'
        );
    }

    /**
     * All values in all layers must remain bounded over a very long simulation.
     */
    public function test_all_values_bounded_over_100k_years(): void
    {
        $snapshot = WorldSnapshot::defaultObservation(0);

        for ($i = 0; $i < 1000; $i++) {
            $snapshot = $this->pipeline->step($snapshot);

            $this->assertGreaterThanOrEqual(0.0, $snapshot->cosmic->entropy);
            $this->assertLessThanOrEqual(1.0, $snapshot->cosmic->entropy);
            $this->assertGreaterThanOrEqual(0.0, $snapshot->cosmic->energy);
            $this->assertLessThanOrEqual(1.0, $snapshot->cosmic->energy);

            $this->assertGreaterThanOrEqual(0.0, $snapshot->environment->leyEnergy);
            $this->assertLessThanOrEqual(1.0, $snapshot->environment->leyEnergy);
            $this->assertGreaterThanOrEqual(0.0, $snapshot->environment->terrainStability);
            $this->assertLessThanOrEqual(1.0, $snapshot->environment->terrainStability);

            $this->assertGreaterThanOrEqual(0.0, $snapshot->civilization->collectiveKnowledge);
            $this->assertLessThanOrEqual(2.0, $snapshot->civilization->collectiveKnowledge);
            $this->assertGreaterThanOrEqual(0.0, $snapshot->civilization->ritualCoherence);
            $this->assertLessThanOrEqual(1.0, $snapshot->civilization->ritualCoherence);
        }
    }

    /**
     * The coupling must be asymmetric.
     */
    public function test_coupling_is_asymmetric(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.70,
                energy: 0.40,
                causality: 0.60,
                strain: 0.60,
                stability: 0.30,
                currentAttractor: 'HIGH_CHAOS',
                year: 0
            ),
            environment: EnvironmentState::defaultObservation(0),
            civilization: CivilizationState::defaultObservation(0),
            year: 0,
        );

        $next = $this->pipeline->step($snapshot);

        $envChange = abs($next->environment->terrainStability - $snapshot->environment->terrainStability);
        $this->assertGreaterThan(0.0, $envChange, 'Environment must respond to cosmic changes');
    }

    /**
     * WorldSnapshot serialization round-trip.
     */
    public function test_world_snapshot_roundtrip(): void
    {
        $snapshot = WorldSnapshot::defaultObservation(42);
        $array = $snapshot->toArray();
        $restored = WorldSnapshot::fromArray($array);
        $this->assertEquals($snapshot->toArray(), $restored->toArray());
    }

    /**
     * Environmental tipping point must trigger cascade.
     */
    public function test_environment_tipping_point(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.80,
                energy: 0.30,
                causality: 0.70,
                strain: 0.50,
                stability: 0.20,
                currentAttractor: 'HIGH_CHAOS',
                year: 0
            ),
            environment: new EnvironmentState(
                leyEnergy: 0.40,
                terrainStability: 0.30,
                biosphereVitality: 0.40,
                anomalyDensity: 0.65,
                year: 0,
            ),
            civilization: CivilizationState::defaultObservation(0),
            year: 0,
        );

        $next = $this->pipeline->step($snapshot);

        $totalHealth = $next->environment->terrainStability + $next->environment->biosphereVitality;
        $initialHealth = $snapshot->environment->terrainStability + $snapshot->environment->biosphereVitality;

        $this->assertLessThanOrEqual(
            $initialHealth,
            $totalHealth,
            'Tipping point should degrade environmental health'
        );
    }

    // === PHASE 3 TESTS: Bifurcation ===

    /**
     * Minor bifurcation must occur under sufficient strain.
     */
    public function test_minor_bifurcation_occurs(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.60,
                energy: 0.45,
                causality: 0.50,
                strain: 0.95, // Above EQUILIBRIUM threshold (0.90)
                stability: 0.40,
                currentAttractor: 'EQUILIBRIUM',
                year: 5000
            ),
            environment: EnvironmentState::defaultObservation(5000),
            civilization: CivilizationState::defaultObservation(5000),
            year: 5000,
        );

        $result = $this->bifurcationManager->evaluate($snapshot);

        $this->assertTrue($result['bifurcated'], 'Should bifurcate when strain exceeds threshold');
        $this->assertEquals('MINOR_BIFURCATION', $result['event']['type']);
        $this->assertNotEquals('EQUILIBRIUM', $result['snapshot']->cosmic->currentAttractor);
    }

    /**
     * Major bifurcation creates a new attractor.
     */
    public function test_major_bifurcation_creates_new_attractor(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.60,
                energy: 0.45,
                causality: 0.80,
                strain: 1.50, // Way above threshold (0.90) → excess > 0.5
                stability: 0.40,
                currentAttractor: 'EQUILIBRIUM',
                year: 10000
            ),
            environment: EnvironmentState::defaultObservation(10000),
            civilization: new CivilizationState(
                collectiveKnowledge: 1.50,
                ritualCoherence: 0.95,
                technologicalLevel: 0.80,
                factionStability: 0.60,
                resonanceAccumulator: 0.70, // > 0.6 required for major
                year: 10000,
            ),
            year: 10000,
        );

        $result = $this->bifurcationManager->evaluate($snapshot);

        $this->assertTrue($result['bifurcated'], 'Should trigger major bifurcation');
        $this->assertEquals('MAJOR_BIFURCATION', $result['event']['type']);
        $this->assertStringContains('EMERGENT_', $result['snapshot']->cosmic->currentAttractor);

        // New attractor must be in registry
        $newCode = $result['snapshot']->cosmic->currentAttractor;
        $this->assertNotNull($this->bifurcationManager->findAttractor($newCode));
    }

    /**
     * No bifurcation when strain is below threshold.
     */
    public function test_no_bifurcation_below_threshold(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.20,
                energy: 0.60,
                causality: 0.30,
                strain: 0.40, // Well below threshold
                stability: 0.80,
                currentAttractor: 'EQUILIBRIUM',
                year: 1000
            ),
            environment: EnvironmentState::defaultObservation(1000),
            civilization: CivilizationState::defaultObservation(1000),
            year: 1000,
        );

        $result = $this->bifurcationManager->evaluate($snapshot);

        $this->assertFalse($result['bifurcated']);
        $this->assertNull($result['event']);
    }

    /**
     * Bifurcation history must be tracked.
     */
    public function test_bifurcation_history_tracked(): void
    {
        $snapshot = new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: 0.60,
                energy: 0.45,
                causality: 0.50,
                strain: 0.95,
                stability: 0.40,
                currentAttractor: 'EQUILIBRIUM',
                year: 5000
            ),
            environment: EnvironmentState::defaultObservation(5000),
            civilization: CivilizationState::defaultObservation(5000),
            year: 5000,
        );

        $this->bifurcationManager->evaluate($snapshot);

        $history = $this->bifurcationManager->getHistory();
        $this->assertNotEmpty($history);
        $this->assertEquals(5000, $history[0]['year']);
    }

    /**
     * Narrative renderer must produce non-empty output.
     */
    public function test_narrative_renderer_produces_output(): void
    {
        $renderer = new CosmicNarrativeRenderer();
        $snapshot = WorldSnapshot::defaultObservation(0);

        $output = $renderer->render($snapshot);

        $this->assertNotEmpty($output['cosmic']);
        $this->assertNotEmpty($output['environment']);
        $this->assertNotEmpty($output['civilization']);
        $this->assertNotEmpty($output['tension']);
    }

    /**
     * Narrative renderer must describe bifurcation events.
     */
    public function test_narrative_renderer_describes_events(): void
    {
        $renderer = new CosmicNarrativeRenderer();
        $snapshot = WorldSnapshot::defaultObservation(0);

        $events = [
            ['type' => 'MINOR_BIFURCATION', 'year' => 5000, 'from' => 'EQUILIBRIUM', 'to' => 'HIGH_CHAOS', 'force' => 1.2],
            ['type' => 'MAJOR_BIFURCATION', 'year' => 10000, 'from' => 'HIGH_CHAOS', 'to' => 'EMERGENT_10000', 'force' => 1.8],
        ];

        $output = $renderer->render($snapshot, $events);

        $this->assertCount(2, $output['events']);
        $this->assertStringContainsString('5000', $output['events'][0]);
        $this->assertStringContainsString('ĐẠI THIÊN BIẾN', $output['events'][1]);
    }

    /**
     * Simulate method must return events from the run.
     */
    public function test_simulate_returns_events(): void
    {
        $initial = WorldSnapshot::defaultObservation(0);
        $result = $this->pipeline->simulate($initial, 10);

        $this->assertArrayHasKey('trajectory', $result);
        $this->assertArrayHasKey('events', $result);
        $this->assertCount(11, $result['trajectory']);
    }

    /**
     * Helper method for string contains assertion.
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Expected '{$haystack}' to contain '{$needle}'"
        );
    }
}
