<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Arc\Service;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Arc\Service\ArcPlanner;
use WorldOS\Saga\Domain\Arc\ValueObject\ArcType;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class ArcPlannerTest extends TestCase
{
    private ArcPlanner $planner;

    protected function setUp(): void
    {
        $this->planner = new ArcPlanner();
    }

    public function test_empty_timeline_returns_no_arcs(): void
    {
        $this->assertEmpty($this->planner->plan([]));
    }

    public function test_flat_tension_creates_single_inciting_arc(): void
    {
        $timeline = [];
        $state = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.1);
        
        for ($i = 0; $i < 20; $i++) {
            $timeline[$i] = $state;
        }

        $arcs = $this->planner->plan($timeline);

        // It should just be one long inciting arc because tension never jumped
        $this->assertCount(1, $arcs);
        $this->assertSame(ArcType::INCITING, $arcs[0]->getType());
        $this->assertSame(0, $arcs[0]->getStartTick());
        $this->assertSame(19, $arcs[0]->getEndTick());
        $this->assertSame(0.0, $arcs[0]->getTensionDelta());
    }

    public function test_rising_tension_creates_escalation_arc(): void
    {
        $timeline = [];
        
        // Ticks 0-4: Low tension (INCITING)
        for ($i = 0; $i < 5; $i++) {
            $timeline[$i] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.1);
        }

        // Ticks 5-10: Rapid tension climb (ESCALATION)
        for ($i = 5; $i <= 10; $i++) {
            $tension = 0.1 + (($i - 4) * 0.1); // jumps 0.1 per tick
            $timeline[$i] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, $tension);
        }

        $arcs = $this->planner->plan($timeline);

        $this->assertGreaterThan(1, count($arcs));
        $this->assertSame(ArcType::INCITING, $arcs[0]->getType());
        $this->assertSame(ArcType::ESCALATION, $arcs[1]->getType());
    }

    public function test_tension_peak_creates_climax_and_resolution(): void
    {
        $timeline = [];
        
        // Ticks 0-2: ESCALATION (Starting high)
        $timeline[0] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.5);
        $timeline[1] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.7);
        $timeline[2] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.85); // Pushes past 0.8 -> CLIMAX

        // Ticks 3-4: CLIMAX (Tension peaks)
        $timeline[3] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.9);
        $timeline[4] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.95);

        // Ticks 5-7: RESOLUTION (Tension drops massively -> Δ < -0.1)
        $timeline[5] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.7);
        $timeline[6] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.4);
        $timeline[7] = StateVector::genesis()->withDimension(StateVector::DIMENSION_COSMIC_TENSION, 0.2);

        $arcs = $this->planner->plan($timeline);

        // Debug assertions if needed, but we expect at least Escalation, Climax, Resolution sequences.
        $types = array_map(fn($arc) => $arc->getType(), $arcs);
        
        $this->assertContains(ArcType::CLIMAX, $types, "Timeline lacking expected CLIMAX arc.");
        $this->assertContains(ArcType::RESOLUTION, $types, "Timeline lacking expected RESOLUTION arc.");
    }
}
