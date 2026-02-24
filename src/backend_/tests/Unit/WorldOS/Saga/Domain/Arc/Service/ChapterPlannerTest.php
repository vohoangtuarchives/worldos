<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Domain\Arc\Service;

use PHPUnit\Framework\TestCase;
use WorldOS\Saga\Domain\Arc\Service\ChapterPlanner;
use WorldOS\Saga\Domain\Arc\ValueObject\Arc;
use WorldOS\Saga\Domain\Arc\ValueObject\ArcType;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

final class ChapterPlannerTest extends TestCase
{
    private ChapterPlanner $planner;

    protected function setUp(): void
    {
        $this->planner = new ChapterPlanner();
    }

    public function test_dominant_dimension_selected_by_max_delta(): void
    {
        $arc = Arc::create(ArcType::INCITING, 10, 12, 0.0);
        
        // Base state
        $base = StateVector::genesis();

        $timeline = [
            10 => $base,
            // Tick 11: Power density jumps by 0.3, everything else static
            11 => $base->withDimension(StateVector::DIMENSION_POWER_DENSITY, 0.8),
            // Tick 12: Magic density jumps by 0.5, power density drops by 0.1
            12 => $base->withDimension(StateVector::DIMENSION_POWER_DENSITY, 0.7)
                       ->withDimension(StateVector::DIMENSION_MAGIC_DENSITY, 0.6)
        ];

        $chapters = $this->planner->plan($arc, $timeline);

        $this->assertCount(2, $chapters); // Covers ticks 11 and 12
        
        // Chapter at Tick 11 should focus on Power Density
        $this->assertSame(11, $chapters[0]->getTick());
        $this->assertSame(StateVector::DIMENSION_POWER_DENSITY, $chapters[0]->getDominantDimension());
        
        // Chapter at Tick 12 should focus on Magic Density (0.5 delta > 0.1 delta)
        $this->assertSame(12, $chapters[1]->getTick());
        $this->assertSame(StateVector::DIMENSION_MAGIC_DENSITY, $chapters[1]->getDominantDimension());
    }

    public function test_intensity_scales_with_delta(): void
    {
        $arc = Arc::create(ArcType::CRISIS, 1, 2, 0.0);
        
        $timeline = [
            1 => StateVector::genesis(),
            // Tick 2: Big jump of 0.4
            2 => StateVector::genesis()->withDimension(StateVector::DIMENSION_ENTROPY, 0.4)
        ];

        $chapters = $this->planner->plan($arc, $timeline);

        // Intensity = min(1.0, maxDelta * 2.0) = 0.4 * 2.0 = 0.8
        $this->assertEqualsWithDelta(0.8, $chapters[0]->getIntensity(), 0.001);
    }
}
