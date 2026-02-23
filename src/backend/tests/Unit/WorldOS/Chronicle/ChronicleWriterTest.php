<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Chronicle;

use PHPUnit\Framework\TestCase;
use WorldOS\Chronicle\Domain\Service\ChronicleWriter;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;
use WorldOS\Kernel\Domain\Compiler\PolicyCompiler;
use WorldOS\Kernel\Domain\Compiler\PolicyValidator;
use WorldOS\Kernel\Domain\Preset\KernelPresetFactory;
use WorldOS\Simulation\Domain\Engine\Service\EvolutionOperator;
use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;

class ChronicleWriterTest extends TestCase
{
    private ChronicleWriter $writer;

    protected function setUp(): void
    {
        $this->writer = new ChronicleWriter();
    }

    private function makeTickResult(
        StateVector $state,
        array $anomalies = [],
        int $tick = 1,
        int $seed = 42
    ): TickResult {
        return new TickResult(
            tick:            $tick,
            seed:            $seed,
            nextStateVector: $state,
            entropyDelta:    0.01,
            existenceWeight: 0.8,
            anomalies:       $anomalies
        );
    }

    public function test_anomaly_produces_anomaly_spike_event()
    {
        $anomaly = new AnomalyEvent(StateVector::DIMENSION_COSMIC_TENSION, 0.9, 0.75, 0.6);
        $result  = $this->makeTickResult(StateVector::genesis(), [$anomaly]);

        $events = $this->writer->record('universe-1', $result);

        $this->assertCount(1, $events);
        $this->assertEquals(EventType::ANOMALY_SPIKE, $events[0]->getType());
        $this->assertEquals('universe-1', $events[0]->getUniverseId());
    }

    public function test_fork_triggered_produces_fork_event()
    {
        $result = $this->makeTickResult(StateVector::genesis());

        $events = $this->writer->record('universe-1', $result, forkTriggered: true);

        $forkEvents = array_filter($events, fn($e) => $e->getType() === EventType::FORK_TRIGGERED);
        $this->assertCount(1, $forkEvents);
        $this->assertEquals(Severity::CRITICAL, array_values($forkEvents)[0]->getSeverity());
    }

    public function test_transcendence_threshold_triggers_event()
    {
        $state  = StateVector::genesis()->withDimension(StateVector::DIMENSION_TRANSCENDENCE, 0.90);
        $result = $this->makeTickResult($state);

        $events = $this->writer->record('universe-1', $result);

        $transcendenceEvents = array_filter($events, fn($e) => $e->getType() === EventType::TRANSCENDENCE);
        $this->assertCount(1, $transcendenceEvents);
    }

    public function test_critical_entropy_triggers_collapse_event()
    {
        $state  = StateVector::genesis()->withDimension(StateVector::DIMENSION_ENTROPY, 0.97);
        $result = $this->makeTickResult($state);

        $events = $this->writer->record('universe-1', $result);

        $collapseEvents = array_filter($events, fn($e) => $e->getType() === EventType::COLLAPSE);
        $this->assertCount(1, $collapseEvents);
        $this->assertEquals(Severity::CRITICAL, array_values($collapseEvents)[0]->getSeverity());
    }

    public function test_no_events_for_normal_quiet_tick()
    {
        $result = $this->makeTickResult(StateVector::genesis());

        $events = $this->writer->record('universe-1', $result, forkTriggered: false);

        $this->assertEmpty($events);
    }

    public function test_severity_scales_with_anomaly_intensity()
    {
        $low  = new AnomalyEvent(StateVector::DIMENSION_ENTROPY, 0.8, 0.75, 0.1);
        $high = new AnomalyEvent(StateVector::DIMENSION_ENTROPY, 0.9, 0.75, 0.8);

        $resultLow  = $this->makeTickResult(StateVector::genesis(), [$low]);
        $resultHigh = $this->makeTickResult(StateVector::genesis(), [$high]);

        $eventsLow  = $this->writer->record('u-1', $resultLow);
        $eventsHigh = $this->writer->record('u-2', $resultHigh);

        $this->assertEquals(Severity::LOW, $eventsLow[0]->getSeverity());
        $this->assertEquals(Severity::HIGH, $eventsHigh[0]->getSeverity());
    }
}
