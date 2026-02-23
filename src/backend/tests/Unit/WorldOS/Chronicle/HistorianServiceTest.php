<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Chronicle;

use PHPUnit\Framework\TestCase;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\Service\HistorianService;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;

class HistorianServiceTest extends TestCase
{
    private HistorianService $service;

    protected function setUp(): void
    {
        $this->service = new HistorianService();
    }

    public function test_synthesize_eras_splits_by_severity(): void
    {
        // 1. Era 1: Low entropy period
        $e1 = ChronicleEvent::record('u1', 10, 42, EventType::ANOMALY_SPIKE, 'Minor event', Severity::LOW);
        $e2 = ChronicleEvent::record('u1', 20, 42, EventType::ANOMALY_SPIKE, 'Minor event', Severity::LOW);
        
        // 2. Breakpoint: Critical event at tick 100
        $e3 = ChronicleEvent::record('u1', 100, 42, EventType::ANOMALY_SPIKE, 'MASSIVE COLLAPSE', Severity::CRITICAL);
        
        // 3. Era 2: Post collapse
        $e4 = ChronicleEvent::record('u1', 110, 42, EventType::ANOMALY_SPIKE, 'Recovery', Severity::LOW);

        $eras = $this->service->synthesizeEras([$e1, $e2, $e3, $e4]);

        $this->assertCount(2, $eras);
        $this->assertEquals(10, $eras[0]->startTick);
        $this->assertEquals(99, $eras[0]->endTick);
        $this->assertEquals('prosperity', $eras[0]->theme);

        $this->assertEquals(100, $eras[1]->startTick);
        $this->assertEquals(110, $eras[1]->endTick);
        $this->assertEquals('collapse', $eras[1]->theme);
    }

    public function test_synthesize_eras_splits_by_time_gap(): void
    {
        $e1 = ChronicleEvent::record('u1', 10, 42, EventType::ANOMALY_SPIKE, 'Event 1', Severity::LOW);
        // Gap of 2000 ticks
        $e2 = ChronicleEvent::record('u1', 2010, 42, EventType::ANOMALY_SPIKE, 'Event 2', Severity::LOW);

        $eras = $this->service->synthesizeEras([$e1, $e2]);

        $this->assertCount(2, $eras);
        $this->assertEquals(10, $eras[0]->startTick);
        $this->assertEquals(2009, $eras[0]->endTick);
        $this->assertEquals(2010, $eras[1]->startTick);
    }
}
