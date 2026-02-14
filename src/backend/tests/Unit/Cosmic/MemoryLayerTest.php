<?php

declare(strict_types=1);

namespace Tests\Unit\Cosmic;

use App\Domains\Cosmic\Entities\AttractorAggregate;
use App\Domains\Cosmic\Services\CollectiveFieldService;
use App\Domains\Cosmic\ValueObjects\Attractor;
use App\Domains\Cosmic\ValueObjects\IndividualMemory;
use App\Domains\Cosmic\ValueObjects\InteractionMemory;
use PHPUnit\Framework\TestCase;

class MemoryLayerTest extends TestCase
{
    // --- IndividualMemory Tests ---

    public function test_individual_memory_initial(): void
    {
        $mem = IndividualMemory::initial('EQUILIBRIUM');
        $this->assertEquals('EQUILIBRIUM', $mem->getAttractorCode());
        $this->assertEquals(0.0, $mem->getCumulativeRebirthGain());
        $this->assertEquals(0.0, $mem->getCumulativeInstability());
        $this->assertEquals(0, $mem->getEventCount());
        $this->assertEquals(0.0, $mem->identityKarmaIndex());
    }

    public function test_individual_memory_record_event(): void
    {
        $mem = IndividualMemory::initial('EQUILIBRIUM');
        $mem2 = $mem->recordEvent(['entropy' => 0.1, 'energy' => 0.05]);

        $this->assertEquals(1, $mem2->getEventCount());
        $inertia = $mem2->getInertiaVector();
        $this->assertEqualsWithDelta(0.1, $inertia['entropy'], 0.001);
        $this->assertEqualsWithDelta(0.05, $inertia['energy'], 0.001);

        // Original unchanged (immutable)
        $this->assertEquals(0, $mem->getEventCount());
    }

    public function test_individual_memory_decay(): void
    {
        $mem = IndividualMemory::initial('EQUILIBRIUM')
            ->recordEvent(['entropy' => 1.0, 'energy' => 1.0]);

        // Decay multiple times
        $decayed = $mem;
        for ($i = 0; $i < 100; $i++) {
            $decayed = $decayed->decay();
        }

        // After 100 decays (λ=0.985^100 ≈ 0.22), values should be much smaller
        $inertia = $decayed->getInertiaVector();
        $this->assertLessThan(0.3, $inertia['entropy']);
    }

    public function test_individual_memory_bias_bounded(): void
    {
        // Create a memory with large inertia
        $mem = IndividualMemory::initial('EQUILIBRIUM');
        for ($i = 0; $i < 50; $i++) {
            $mem = $mem->recordEvent([
                'entropy' => 0.5,
                'energy' => 0.5,
                'causality' => 0.5,
                'strain' => 0.5,
                'stability' => 0.5,
            ]);
        }

        $bias = $mem->memoryBias();

        // Calculate magnitude
        $magnitude = 0.0;
        foreach ($bias as $v) {
            $magnitude += $v * $v;
        }
        $magnitude = sqrt($magnitude);

        $this->assertLessThanOrEqual(0.25 + 0.001, $magnitude, 'Memory bias magnitude must be ≤ 0.25');
    }

    public function test_individual_memory_iki(): void
    {
        $mem = IndividualMemory::initial('EQUILIBRIUM')
            ->recordRebirth(0.3, 0.2)
            ->recordRebirth(0.1, 0.15);

        $iki = $mem->identityKarmaIndex();
        $this->assertGreaterThan(0.0, $iki);

        // IKI = 0.6 * (0.2 + 0.15) + 0.4 * (0.3 + 0.1) = 0.21 + 0.16 = 0.37
        $this->assertEqualsWithDelta(0.37, $iki, 0.01);
    }

    public function test_individual_memory_serialization(): void
    {
        $mem = IndividualMemory::initial('HIGH_CHAOS')
            ->recordEvent(['entropy' => 0.3])
            ->recordRebirth(0.2, 0.1)
            ->recordInstability(0.5);

        $array = $mem->toArray();
        $restored = IndividualMemory::fromArray($array);

        $this->assertEquals($mem->getAttractorCode(), $restored->getAttractorCode());
        $this->assertEquals($mem->getCumulativeRebirthGain(), $restored->getCumulativeRebirthGain());
        $this->assertEquals($mem->getCumulativeInstability(), $restored->getCumulativeInstability());
        $this->assertEquals($mem->getEventCount(), $restored->getEventCount());
    }

    // --- InteractionMemory Tests ---

    public function test_interaction_memory_initial(): void
    {
        $ir = InteractionMemory::initial('EQUILIBRIUM', 'HIGH_CHAOS');
        $this->assertEquals(0.0, $ir->hrScore());
        $this->assertEquals(1.0, $ir->forceModifier());
        $this->assertEquals(0, $ir->interactionCount);
    }

    public function test_interaction_memory_consistent_ordering(): void
    {
        $ir1 = InteractionMemory::initial('HIGH_CHAOS', 'EQUILIBRIUM');
        $ir2 = InteractionMemory::initial('EQUILIBRIUM', 'HIGH_CHAOS');

        // Should have same key ordering regardless of input order
        $this->assertEquals($ir1->attractorA, $ir2->attractorA);
        $this->assertEquals($ir1->attractorB, $ir2->attractorB);
    }

    public function test_interaction_memory_hr_score(): void
    {
        $ir = InteractionMemory::initial('EQUILIBRIUM', 'RESONANCE_DOMINANT')
            ->recordSharedSurvival(0.5)
            ->recordConflict(0.2)
            ->recordRebirthAlignment(0.3);

        $hr = $ir->hrScore();
        // HR = 0.4*0.5 - 0.35*0.2 + 0.25*0.3 = 0.20 - 0.07 + 0.075 = 0.205
        $this->assertGreaterThan(0.0, $hr);
        $this->assertLessThanOrEqual(0.3, abs($hr), 'HR must be bounded by 0.3');
    }

    public function test_interaction_memory_hr_clamped(): void
    {
        // Max out shared survival → HR should still be ≤ 0.3
        $ir = InteractionMemory::initial('A', 'B')
            ->recordSharedSurvival(1.0)
            ->recordRebirthAlignment(1.0);

        $this->assertLessThanOrEqual(0.3, $ir->hrScore());

        // Max out conflict → HR should still be ≥ -0.3
        $ir2 = InteractionMemory::initial('A', 'B')
            ->recordConflict(1.0);

        $this->assertGreaterThanOrEqual(-0.3, $ir2->hrScore());
    }

    public function test_interaction_memory_force_modifier(): void
    {
        $cooperative = InteractionMemory::initial('A', 'B')
            ->recordSharedSurvival(0.8);

        $this->assertGreaterThan(1.0, $cooperative->forceModifier());

        $adversarial = InteractionMemory::initial('A', 'B')
            ->recordConflict(0.8);

        $this->assertLessThan(1.0, $adversarial->forceModifier());
    }

    public function test_interaction_memory_serialization(): void
    {
        $ir = InteractionMemory::initial('EQUILIBRIUM', 'HIGH_CHAOS')
            ->recordSharedSurvival(0.3)
            ->recordConflict(0.1);

        $array = $ir->toArray();
        $restored = InteractionMemory::fromArray($array);

        $this->assertEquals($ir->attractorA, $restored->attractorA);
        $this->assertEqualsWithDelta($ir->hrScore(), $restored->hrScore(), 0.001);
    }

    // --- CollectiveFieldService (GCMF) Tests ---

    public function test_gcmf_initial(): void
    {
        $gcmf = new CollectiveFieldService(0.0);
        $this->assertEquals(0.0, $gcmf->getCurrentValue());
        $this->assertEquals('BALANCED_NEUTRAL', $gcmf->epochMood());
    }

    public function test_gcmf_decays_over_time(): void
    {
        $gcmf = new CollectiveFieldService(0.5);

        // Update with no attractors → should decay
        for ($i = 0; $i < 50; $i++) {
            $gcmf->update([], $i);
        }

        $this->assertLessThan(0.2, $gcmf->getCurrentValue());
    }

    public function test_gcmf_positive_contributions(): void
    {
        $gcmf = new CollectiveFieldService(0.0);

        // Create an attractor with positive morph + rebirth
        $attractor = AttractorAggregate::fromAttractor(Attractor::find('EQUILIBRIUM'), 0);
        $attractor->morph(
            newCentroid: ['entropy' => 0.2, 'energy' => 0.7, 'causality' => 0.5, 'strain' => 0.1, 'stability' => 0.8],
            newSemantic: ['order' => 0.8],
            rebirthGain: 0.5,
            morphIntensity: 0.3,
            tick: 1000,
        );

        $gcmf->update([$attractor], 1);
        $this->assertGreaterThan(0.0, $gcmf->getCurrentValue());
    }

    public function test_gcmf_bias_bounded(): void
    {
        $gcmf = new CollectiveFieldService(0.8);
        $bias = $gcmf->gcmfBias();

        $magnitude = 0.0;
        foreach ($bias as $v) {
            $magnitude += $v * $v;
        }
        $magnitude = sqrt($magnitude);

        // Each component uses fraction of MAX_GCMF_BIAS (0.25), total should be reasonable
        foreach ($bias as $dim => $val) {
            $this->assertLessThanOrEqual(0.25, abs($val), "GCMF bias per dimension must be bounded");
        }
    }

    public function test_gcmf_epoch_moods(): void
    {
        $this->assertEquals('TRANSCENDENT_ASCENDING', (new CollectiveFieldService(0.7))->epochMood());
        $this->assertEquals('PROSPEROUS_ORDER', (new CollectiveFieldService(0.3))->epochMood());
        $this->assertEquals('BALANCED_NEUTRAL', (new CollectiveFieldService(0.0))->epochMood());
        $this->assertEquals('TURBULENT_DECLINE', (new CollectiveFieldService(-0.3))->epochMood());
        $this->assertEquals('DARK_COLLAPSE', (new CollectiveFieldService(-0.6))->epochMood());
    }

    public function test_gcmf_emergent_trigger(): void
    {
        $gcmf = new CollectiveFieldService(0.5);

        // All conditions met → should trigger
        $this->assertTrue($gcmf->shouldTriggerEmergentCheck(0.5, 0.6, 0.4));

        // GCMF too low → should not trigger
        $low = new CollectiveFieldService(0.1);
        $this->assertFalse($low->shouldTriggerEmergentCheck(0.5, 0.6, 0.4));
    }

    public function test_gcmf_serialization(): void
    {
        $gcmf = new CollectiveFieldService(0.4);
        $gcmf->update([], 0);
        $gcmf->update([], 1);

        $array = $gcmf->toArray();
        $restored = CollectiveFieldService::fromArray($array);

        $this->assertEqualsWithDelta($gcmf->getCurrentValue(), $restored->getCurrentValue(), 0.001);
        $this->assertCount(2, $restored->getHistory());
    }
}
