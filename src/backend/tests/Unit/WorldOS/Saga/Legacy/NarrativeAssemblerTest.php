<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Saga\Legacy;

use PHPUnit\Framework\TestCase;
use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\Service\HistorianService;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;
use WorldOS\Saga\Domain\Legacy\NarrativeAssembler;

class NarrativeAssemblerTest extends TestCase
{
    private NarrativeAssembler $assembler;

    protected function setUp(): void
    {
        $this->assembler = new NarrativeAssembler(new HistorianService());
    }

    public function test_assemble_chronicle_returns_structured_narrative(): void
    {
        $events = [
            ChronicleEvent::record('u1', 10, 42, EventType::ANOMALY_SPIKE, 'Birth', Severity::LOW),
            ChronicleEvent::record('u1', 100, 42, EventType::ANOMALY_SPIKE, 'Chaos', Severity::CRITICAL),
            ChronicleEvent::record('u1', 110, 42, EventType::ANOMALY_SPIKE, 'New Life', Severity::LOW),
        ];

        $output = $this->assembler->assembleChronicle($events);

        // Verify structure
        $this->assertStringContainsString('--- Kỷ nguyên', $output);
        $this->assertStringContainsString('Kỷ nguyên Hoàng kim', $output);
        $this->assertStringContainsString('Kỷ nguyên Tro tàn', $output);
        $this->assertStringContainsString('Giai đoạn Kỷ nguyên', $output);
    }
}
