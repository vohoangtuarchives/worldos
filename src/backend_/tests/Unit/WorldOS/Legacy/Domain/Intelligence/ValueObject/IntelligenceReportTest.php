<?php

namespace Tests\Unit\WorldOS\Legacy\Domain\Intelligence\ValueObject;

use PHPUnit\Framework\TestCase;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceReport;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceSource;
use WorldOS\Legacy\Domain\Intelligence\ValueObject\IntelligenceType;

final class IntelligenceReportTest extends TestCase
{
    public function test_is_reliable_and_urgency(): void
    {
        $source = new IntelligenceSource('environment', 'w1', 0.9);
        $report = new IntelligenceReport(
            'r1',
            IntelligenceType::ENVIRONMENTAL_SCAN,
            $source,
            'content',
            [],
            new \DateTimeImmutable(),
            0.85,
            1
        );
        $this->assertTrue($report->isReliable());
        $this->assertSame('low', $report->getUrgency());
    }

    public function test_with_age_and_decayed_accuracy(): void
    {
        $source = IntelligenceSource::environment('w1');
        $report = new IntelligenceReport(
            'r1',
            IntelligenceType::CHARACTER_OBSERVATION,
            $source,
            'content',
            [],
            new \DateTimeImmutable(),
            0.8,
            2
        );
        $older = $report->withAge(15);
        $this->assertSame(15, $older->age);
        $decayed = $report->withDecayedAccuracy(0.1);
        $this->assertEqualsWithDelta(0.7, $decayed->accuracy, 1e-9);
    }

    public function test_to_array(): void
    {
        $source = IntelligenceSource::event('e1', 0.8);
        $report = new IntelligenceReport(
            'id-1',
            IntelligenceType::EVENT_ANALYSIS,
            $source,
            'content',
            ['urgency' => 'high'],
            new \DateTimeImmutable('2025-01-01 12:00:00'),
            0.75,
            0
        );
        $arr = $report->toArray();
        $this->assertSame('id-1', $arr['id']);
        $this->assertSame('event_analysis', $arr['type']);
        $this->assertSame('content', $arr['content']);
        $this->assertSame('high', $arr['urgency']);
    }
}
