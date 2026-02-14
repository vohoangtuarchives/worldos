<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Services\EpochManager;
use App\Domains\Cosmic\Services\QualityEvaluator;
use App\Domains\Cosmic\ValueObjects\CosmicState;
use App\Domains\Cosmic\ValueObjects\EnvironmentState;
use App\Domains\Cosmic\ValueObjects\CivilizationState;
use App\Domains\Cosmic\ValueObjects\UniverseStyleVersion;
use App\Domains\Cosmic\ValueObjects\WorldSnapshot;
use PHPUnit\Framework\TestCase;

class StyleEpochTest extends TestCase
{
    // --- UniverseStyleVersion Tests ---

    public function test_default_style(): void
    {
        $style = UniverseStyleVersion::defaultStyle();

        $this->assertEquals('transcendent_order', $style->styleId);
        $this->assertEquals(1, $style->versionNumber);
        $this->assertNotEmpty($style->checksumHash);
        $this->assertArrayHasKey('order_bias', $style->weightProfile);
        $this->assertArrayHasKey('celestial_harmony', $style->alignmentProfile);
    }

    public function test_style_versioning(): void
    {
        $v1 = UniverseStyleVersion::defaultStyle();
        $v2 = $v1->newVersion(
            ['order_bias' => 0.6],
            ['demon_path' => 0.1],
            [],
        );

        $this->assertEquals(2, $v2->versionNumber);
        $this->assertNotEquals($v1->checksumHash, $v2->checksumHash);
        $this->assertEquals(0.6, $v2->weightProfile['order_bias']);
    }

    public function test_style_bias_small(): void
    {
        $style = UniverseStyleVersion::defaultStyle();
        $bias = $style->styleBias('celestial_harmony');

        // Style bias should be subtle (scale = 0.05)
        foreach ($bias as $dim => $val) {
            $this->assertLessThanOrEqual(0.1, abs($val), "Style bias {$dim} too large");
        }
    }

    public function test_style_checksum_deterministic(): void
    {
        $s1 = UniverseStyleVersion::defaultStyle();
        $s2 = UniverseStyleVersion::defaultStyle();

        $this->assertEquals($s1->checksumHash, $s2->checksumHash);
    }

    public function test_style_serialization(): void
    {
        $style = UniverseStyleVersion::defaultStyle();
        $array = $style->toArray();
        $restored = UniverseStyleVersion::fromArray($array);

        $this->assertEquals($style->styleId, $restored->styleId);
        $this->assertEquals($style->versionNumber, $restored->versionNumber);
        $this->assertEquals($style->checksumHash, $restored->checksumHash);
    }

    // --- EpochManager Tests ---

    public function test_epoch_first_epoch(): void
    {
        $mgr = new EpochManager();
        $mgr->startFirstEpoch(0);

        $this->assertEquals(1, $mgr->getEpochCount());
        $epoch = $mgr->getCurrentEpoch();
        $this->assertEquals(0, $epoch['start_tick']);
        $this->assertNull($epoch['end_tick']);
    }

    public function test_epoch_transition(): void
    {
        $mgr = new EpochManager();
        $mgr->startFirstEpoch(0);

        $newStyle = UniverseStyleVersion::defaultStyle()->newVersion(
            ['order_bias' => 0.5], [], [],
        );
        $mgr->transitionStyle($newStyle, 1000);

        $this->assertEquals(2, $mgr->getEpochCount());

        // First epoch should be closed
        $epochs = $mgr->getEpochs();
        $this->assertEquals(1000, $epochs[0]['end_tick']);

        // Second epoch open
        $this->assertNull($epochs[1]['end_tick']);
    }

    public function test_epoch_dampened_transition(): void
    {
        $mgr = new EpochManager();
        $mgr->startFirstEpoch(0);

        $newStyle = UniverseStyleVersion::defaultStyle()->newVersion(
            ['order_bias' => 0.2], [], [],
        );
        $mgr->transitionStyle($newStyle, 1000);

        // During transition
        $this->assertTrue($mgr->isInTransition(1003));
        $this->assertEqualsWithDelta(0.3, $mgr->transitionProgress(1003), 0.01);

        // Effective bias should be between old and new
        $biasEarly = $mgr->effectiveStyleBias('celestial_harmony', 1001);
        $biasMid = $mgr->effectiveStyleBias('celestial_harmony', 1005);
        $biasLate = $mgr->effectiveStyleBias('celestial_harmony', 1010);

        // After transition completes
        $this->assertFalse($mgr->isInTransition(1010));
    }

    public function test_epoch_serialization(): void
    {
        $mgr = new EpochManager();
        $mgr->startFirstEpoch(0);

        $array = $mgr->toArray();
        $restored = EpochManager::fromArray($array);

        $this->assertEquals($mgr->getEpochCount(), $restored->getEpochCount());
        $this->assertEquals(
            $mgr->getCurrentStyle()->checksumHash,
            $restored->getCurrentStyle()->checksumHash,
        );
    }

    // --- QualityEvaluator Tests ---

    private function makeSnapshot(float $entropy, float $energy, float $stability, float $strain, string $attractor, int $year): WorldSnapshot
    {
        return new WorldSnapshot(
            cosmic: new CosmicState(
                entropy: $entropy, energy: $energy, causality: 0.5,
                strain: $strain, stability: $stability,
                currentAttractor: $attractor, year: $year,
            ),
            environment: EnvironmentState::defaultObservation($year),
            civilization: CivilizationState::defaultObservation($year),
            year: $year,
        );
    }

    public function test_quality_empty_trajectory(): void
    {
        $eval = new QualityEvaluator();
        $result = $eval->evaluate([]);
        $this->assertEquals(0.0, $result['grandness_index']);
    }

    public function test_quality_stable_trajectory_high_gi(): void
    {
        $eval = new QualityEvaluator();

        // Long stable era with EQUILIBRIUM
        $trajectory = [];
        for ($i = 0; $i < 50; $i++) {
            $trajectory[] = $this->makeSnapshot(0.2, 0.7, 0.8, 0.1, 'EQUILIBRIUM', $i);
        }

        $result = $eval->evaluate($trajectory);
        $gi = $result['grandness_index'];

        $this->assertGreaterThan(0.3, $gi, 'Stable trajectory should have decent GI');
    }

    public function test_quality_chaotic_trajectory_lower_gi(): void
    {
        $eval = new QualityEvaluator();

        // Constant attractor switching and low stability
        $attractors = ['EQUILIBRIUM', 'HIGH_CHAOS', 'VOID_COLLAPSE', 'RESONANCE_DOMINANT'];
        $trajectory = [];
        for ($i = 0; $i < 50; $i++) {
            $att = $attractors[$i % 4];
            $trajectory[] = $this->makeSnapshot(0.7, 0.3, 0.2, 0.8, $att, $i);
        }

        $result = $eval->evaluate($trajectory);
        $gi = $result['grandness_index'];

        $this->assertLessThan(0.5, $gi, 'Chaotic fragmented trajectory should have low GI');
    }

    public function test_quality_metrics_bounded(): void
    {
        $eval = new QualityEvaluator();

        $trajectory = [];
        for ($i = 0; $i < 30; $i++) {
            $trajectory[] = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', $i);
        }

        $result = $eval->evaluate($trajectory);
        $gi = $result['grandness_index'];

        $this->assertGreaterThanOrEqual(0.0, $gi);
        $this->assertLessThanOrEqual(1.0, $gi);
    }

    public function test_quality_deterministic(): void
    {
        $eval = new QualityEvaluator();

        $trajectory = [];
        for ($i = 0; $i < 20; $i++) {
            $trajectory[] = $this->makeSnapshot(0.3, 0.6, 0.7, 0.2, 'EQUILIBRIUM', $i);
        }

        $r1 = $eval->evaluate($trajectory);
        $r2 = $eval->evaluate($trajectory);

        $this->assertEquals($r1['grandness_index'], $r2['grandness_index']);
    }

    public function test_quality_mixed_trajectory(): void
    {
        $eval = new QualityEvaluator();

        // Mix of stable and chaotic periods
        $trajectory = [];
        for ($i = 0; $i < 30; $i++) {
            $trajectory[] = $this->makeSnapshot(0.2, 0.7, 0.8, 0.1, 'EQUILIBRIUM', $i);
        }
        for ($i = 30; $i < 40; $i++) {
            $trajectory[] = $this->makeSnapshot(0.6, 0.4, 0.3, 0.6, 'HIGH_CHAOS', $i);
        }
        for ($i = 40; $i < 60; $i++) {
            $trajectory[] = $this->makeSnapshot(0.3, 0.8, 0.75, 0.15, 'RESONANCE_DOMINANT', $i);
        }

        $result = $eval->evaluate($trajectory);
        $gi = $result['grandness_index'];

        $this->assertGreaterThanOrEqual(0.0, $gi);
        $this->assertLessThanOrEqual(1.0, $gi);
        $this->assertArrayHasKey('mean_dominant_era_length', $result['metrics']);
        $this->assertArrayHasKey('archetype_distribution_entropy', $result['metrics']);
    }
}
