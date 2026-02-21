<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmology\Aggregates\AttractorAggregate;
use App\Domains\Cosmology\Services\MorphingEngine;
use App\Domains\Cosmology\Services\SemanticProjector;
use Tuzy\Domain\Cosmic\ValueObject\Attractor;
use Tuzy\Domain\Cosmology\ValueObject\AttractorIncarnation;
use Tuzy\Domain\Cosmology\ValueObject\CosmicState;
use PHPUnit\Framework\TestCase;

class AttractorMemoryTreeTest extends TestCase
{
    // --- AttractorIncarnation Tests ---

    public function test_incarnation_from_attractor(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $inc = AttractorIncarnation::fromAttractor($attractor, 0);

        $this->assertEquals('EQUILIBRIUM_inc_0', $inc->id);
        $this->assertNull($inc->parentIncarnationId);
        $this->assertEquals(0, $inc->startTick);
        $this->assertNull($inc->endTick);
        $this->assertArrayHasKey('entropy', $inc->centroidSnapshot);
        $this->assertArrayHasKey('order', $inc->semanticSnapshot);
    }

    public function test_incarnation_create_child(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $parent = AttractorIncarnation::fromAttractor($attractor, 0);
        $closed = $parent->close(1000);

        $child = $closed->createChild(
            childIndex: 1,
            startTick: 1000,
            newCentroid: ['entropy' => 0.3, 'energy' => 0.5, 'causality' => 0.4, 'strain' => 0.15, 'stability' => 0.6],
            newSemantic: ['order' => 0.6, 'chaos' => 0.3],
            rebirthGain: 0.15,
            morphIntensity: 0.12,
        );

        $this->assertEquals('EQUILIBRIUM_inc_1', $child->id);
        $this->assertEquals('EQUILIBRIUM_inc_0', $child->parentIncarnationId);
        $this->assertEquals(1000, $child->startTick);
        $this->assertNull($child->endTick);
        $this->assertEquals(0.15, $child->rebirthGainFromParent);
        $this->assertGreaterThan($parent->basinRadius, $child->basinRadius); // Elasticity from RG
    }

    public function test_incarnation_lifespan(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $inc = AttractorIncarnation::fromAttractor($attractor, 0);

        $this->assertEquals(0, $inc->lifespan());
        $this->assertEquals(5000, $inc->lifespan(5000));

        $closed = $inc->close(3000);
        $this->assertEquals(3000, $closed->lifespan());
    }

    public function test_incarnation_serialization_roundtrip(): void
    {
        $attractor = Attractor::find('RESONANCE_DOMINANT');
        $inc = AttractorIncarnation::fromAttractor($attractor, 500);
        $array = $inc->toArray();
        $restored = AttractorIncarnation::fromArray($array);

        $this->assertEquals($inc->id, $restored->id);
        $this->assertEquals($inc->centroidSnapshot, $restored->centroidSnapshot);
        $this->assertEquals($inc->semanticSnapshot, $restored->semanticSnapshot);
    }

    // --- AttractorAggregate Tests ---

    public function test_aggregate_from_attractor(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $this->assertEquals('EQUILIBRIUM', $agg->getCode());
        $this->assertEquals('EMERGENT', $agg->getLifecycleState());
        $this->assertEquals('STABLE', $agg->getPhaseState());
        $this->assertEquals(1, $agg->incarnationDepth());
        $this->assertEquals(0.0, $agg->getCumulativeRebirthGain());
        $this->assertEquals(0.0, $agg->getIdentityKarmaIndex());
    }

    public function test_aggregate_morph_creates_incarnation(): void
    {
        $attractor = Attractor::find('HIGH_CHAOS');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $this->assertEquals(1, $agg->incarnationDepth());

        $agg->morph(
            newCentroid: ['entropy' => 0.5, 'energy' => 0.5, 'causality' => 0.5, 'strain' => 0.1, 'stability' => 0.5],
            newSemantic: ['order' => 0.5, 'chaos' => 0.5],
            rebirthGain: 0.2,
            morphIntensity: 0.15,
            tick: 5000,
        );

        $this->assertEquals(2, $agg->incarnationDepth());
        $this->assertEquals('RECONSOLIDATING', $agg->getPhaseState());
        $this->assertEqualsWithDelta(0.2, $agg->getCumulativeRebirthGain(), 0.001);
    }

    public function test_aggregate_memory_effects_basin_elasticity(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);
        $initialRadius = $agg->getBasinRadius();

        // Successful rebirth → basin grows
        $agg->morph(
            newCentroid: ['entropy' => 0.25, 'energy' => 0.55, 'causality' => 0.4, 'strain' => 0.08, 'stability' => 0.72],
            newSemantic: ['order' => 0.75, 'chaos' => 0.15],
            rebirthGain: 0.3,
            morphIntensity: 0.1,
            tick: 1000,
        );

        $this->assertGreaterThan($initialRadius, $agg->getBasinRadius());
    }

    public function test_aggregate_memory_effects_rigidity(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);
        $initialRigidity = $agg->getRigidityThreshold();

        // Accumulate instability first
        $agg->enterChaos(0.8, 1000);
        $agg->enterChaos(0.5, 2000);

        // Morph triggers memory effects
        $agg->morph(
            newCentroid: ['entropy' => 0.3, 'energy' => 0.5, 'causality' => 0.4, 'strain' => 0.1, 'stability' => 0.6],
            newSemantic: ['order' => 0.6, 'chaos' => 0.3],
            rebirthGain: 0.1,
            morphIntensity: 0.2,
            tick: 3000,
        );

        // With instability > 1.0, rigidity should decrease
        $this->assertLessThan($initialRigidity, $agg->getRigidityThreshold());
    }

    public function test_aggregate_lifecycle_transitions(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $this->assertEquals('EMERGENT', $agg->getLifecycleState());
        $agg->promote();
        $this->assertEquals('DOMINANT', $agg->getLifecycleState());
        $agg->decline();
        $this->assertEquals('DECLINING', $agg->getLifecycleState());
    }

    public function test_aggregate_pull_weight(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        // State very close to centroid → high pull
        $closePull = $agg->pullWeight(['entropy' => 0.2, 'energy' => 0.6, 'causality' => 0.5, 'strain' => 0.1, 'stability' => 0.7]);
        $this->assertGreaterThan(0.5, $closePull);

        // State far from centroid → zero pull (outside basin)
        $farPull = $agg->pullWeight(['entropy' => 0.9, 'energy' => 0.1, 'causality' => 0.1, 'strain' => 0.9, 'stability' => 0.1]);
        $this->assertEquals(0.0, $farPull);
    }

    public function test_aggregate_serialization_roundtrip(): void
    {
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $agg->morph(
            newCentroid: ['entropy' => 0.25, 'energy' => 0.55, 'causality' => 0.4, 'strain' => 0.08, 'stability' => 0.72],
            newSemantic: ['order' => 0.75, 'chaos' => 0.15],
            rebirthGain: 0.2,
            morphIntensity: 0.1,
            tick: 2000,
        );

        $array = $agg->toArray();
        $restored = AttractorAggregate::fromArray($array);

        $this->assertEquals($agg->getCode(), $restored->getCode());
        $this->assertEquals($agg->incarnationDepth(), $restored->incarnationDepth());
        $this->assertEqualsWithDelta($agg->getCumulativeRebirthGain(), $restored->getCumulativeRebirthGain(), 0.001);
        $this->assertEqualsWithDelta($agg->getBasinRadius(), $restored->getBasinRadius(), 0.001);
    }

    // --- MorphingEngine Tests ---

    public function test_cti_calculation(): void
    {
        $engine = new MorphingEngine();
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $state = new CosmicState(
            entropy: 0.3, energy: 0.7, causality: 0.8,
            strain: 0.5, stability: 0.6, currentAttractor: 'EQUILIBRIUM', year: 1000,
        );

        $cti = $engine->calculateCTI($state, $agg);
        $this->assertGreaterThan(0.0, $cti);
        $this->assertLessThan(1.0, $cti); // Should be reasonable
    }

    public function test_morph_step_oscillates(): void
    {
        $engine = new MorphingEngine();
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $state = new CosmicState(
            entropy: 0.5, energy: 0.5, causality: 0.5,
            strain: 0.3, stability: 0.5, currentAttractor: 'EQUILIBRIUM', year: 1000,
        );

        // Step 1: should have nonzero amplitude (sin(ω·1) ≠ 0)
        $result1 = $engine->morphStep($agg, $state, 1);
        $this->assertGreaterThan(0.0, $result1['amplitude']);

        // Later step: amplitude should decrease (damped)
        $result10 = $engine->morphStep($agg, $state, 10);
        $this->assertLessThan($result1['amplitude'], $result10['amplitude']);
    }

    public function test_morph_step_converges(): void
    {
        $engine = new MorphingEngine();
        $attractor = Attractor::find('EQUILIBRIUM');
        $agg = AttractorAggregate::fromAttractor($attractor, 0);

        $state = new CosmicState(
            entropy: 0.5, energy: 0.5, causality: 0.5,
            strain: 0.3, stability: 0.5, currentAttractor: 'EQUILIBRIUM', year: 1000,
        );

        // After enough steps, should converge
        $converged = false;
        for ($i = 0; $i < 100; $i++) {
            $result = $engine->morphStep($agg, $state, $i);
            if ($result['converged']) {
                $converged = true;
                break;
            }
        }
        $this->assertTrue($converged, 'Morph should converge within 100 steps');
    }

    public function test_rebirth_gain_calculation(): void
    {
        $engine = new MorphingEngine();

        // Successful rebirth: order improved
        $rg = $engine->calculateRebirthGain(0.4, 0.7, 10);
        $this->assertGreaterThan(0, $rg);

        // Failed rebirth: order declined
        $rg = $engine->calculateRebirthGain(0.7, 0.4, 10);
        $this->assertLessThan(0, $rg);

        // Edge: zero duration
        $rg = $engine->calculateRebirthGain(0.4, 0.7, 0);
        $this->assertEquals(0.0, $rg);
    }

    public function test_morph_intensity(): void
    {
        $engine = new MorphingEngine();

        $old = ['entropy' => 0.2, 'energy' => 0.6];
        $new = ['entropy' => 0.5, 'energy' => 0.3];
        $intensity = $engine->calculateMorphIntensity($old, $new);

        $this->assertGreaterThan(0.0, $intensity);
        $this->assertLessThan(1.0, $intensity);

        // Same centroid → zero intensity
        $this->assertEquals(0.0, $engine->calculateMorphIntensity($old, $old));
    }

    // --- SemanticProjector Tests ---

    public function test_project_state_bounded(): void
    {
        $projector = new SemanticProjector();

        $state = new CosmicState(
            entropy: 0.5, energy: 0.5, causality: 0.5,
            strain: 0.3, stability: 0.5, currentAttractor: 'EQUILIBRIUM', year: 0,
        );

        $semantic = $projector->projectState($state);

        $this->assertCount(6, $semantic);
        foreach ($semantic as $dim => $value) {
            $this->assertGreaterThanOrEqual(0.0, $value, "Dimension {$dim} should be ≥ 0");
            $this->assertLessThanOrEqual(1.0, $value, "Dimension {$dim} should be ≤ 1");
        }
    }

    public function test_match_archetype_deterministic(): void
    {
        $projector = new SemanticProjector();

        // High order + low chaos → should match celestial or golden age
        $orderlyState = new CosmicState(
            entropy: 0.1, energy: 0.7, causality: 0.6,
            strain: 0.05, stability: 0.9, currentAttractor: 'EQUILIBRIUM', year: 0,
        );

        $semantic = $projector->projectState($orderlyState);
        $match1 = $projector->matchArchetype($semantic);
        $match2 = $projector->matchArchetype($semantic);

        $this->assertEquals($match1['archetype'], $match2['archetype']); // Deterministic
        $this->assertGreaterThan(0.8, $match1['similarity']); // Should be a strong match
    }

    public function test_chaotic_state_matches_chaos_archetype(): void
    {
        $projector = new SemanticProjector();

        $chaoticState = new CosmicState(
            entropy: 0.9, energy: 0.3, causality: 0.2,
            strain: 0.8, stability: 0.1, currentAttractor: 'HIGH_CHAOS', year: 0,
        );

        $semantic = $projector->projectState($chaoticState);
        $match = $projector->matchArchetype($semantic);

        $this->assertContains($match['archetype'], ['demon_path', 'apocalypse']);
    }

    public function test_redundancy_check(): void
    {
        $projector = new SemanticProjector();

        // Vector nearly identical to celestial_harmony → redundant
        $nearCelestial = ['order' => 0.84, 'chaos' => 0.06, 'transcendence' => 0.39, 'decay' => 0.06, 'creation' => 0.49, 'destruction' => 0.06];
        $this->assertTrue($projector->isRedundant($nearCelestial));

        // Unique vector → not redundant
        $unique = ['order' => 0.3, 'chaos' => 0.3, 'transcendence' => 0.9, 'decay' => 0.1, 'creation' => 0.1, 'destruction' => 0.9];
        $this->assertFalse($projector->isRedundant($unique));
    }

    public function test_semantic_slide(): void
    {
        $projector = new SemanticProjector();

        $old = ['order' => 0.8, 'chaos' => 0.1];
        $new = ['order' => 0.2, 'chaos' => 0.9];

        // α = 0 → stays at old
        $slide0 = $projector->semanticSlide($old, $new, 0.0);
        $this->assertEqualsWithDelta(0.8, $slide0['order'], 0.001);

        // α = 1 → reaches new
        $slide1 = $projector->semanticSlide($old, $new, 1.0);
        $this->assertEqualsWithDelta(0.2, $slide1['order'], 0.001);

        // α = 0.5 → midpoint
        $slide05 = $projector->semanticSlide($old, $new, 0.5);
        $this->assertEqualsWithDelta(0.5, $slide05['order'], 0.001);
    }
}
