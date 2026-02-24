<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\Service;

use WorldOS\Chronicle\Domain\Entity\ChronicleEvent;
use WorldOS\Chronicle\Domain\ValueObject\EventType;
use WorldOS\Chronicle\Domain\ValueObject\Severity;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;

/**
 * ChronicleWriter: Domain Service.
 * Inspects a TickResult and emits ChronicleEvents for any significant happenings.
 * Handles: AnomalySpike, Transcendence, Collapse threshold.
 */
final class ChronicleWriter
{
    private const TRANSCENDENCE_THRESHOLD   = 0.85;
    private const COLLAPSE_ENTROPY_THRESHOLD = 0.95;

    /**
     * Analyse a TickResult and return all ChronicleEvents generated for this tick.
     *
     * @param bool $forkTriggered Pass true if ForkDecider::shouldFork() returned true
     * @return ChronicleEvent[]
     */
    public function record(
        string     $universeId,
        TickResult $result,
        bool       $forkTriggered = false
    ): array {
        $events = [];

        // 1. Anomaly Spikes
        foreach ($result->anomalies as $anomaly) {
            $events[] = ChronicleEvent::record(
                universeId: $universeId,
                tick:       $result->tick,
                seed:       $result->seed,
                type:       EventType::ANOMALY_SPIKE,
                title:      sprintf('Anomaly in [%s] reached %.0f%%', $anomaly->dimension, $anomaly->intensity * 100),
                severity:   Severity::fromIntensity($anomaly->intensity),
                payload:    $anomaly->toArray()
            );
        }

        // 2. Fork Triggered
        if ($forkTriggered) {
            $events[] = ChronicleEvent::record(
                universeId: $universeId,
                tick:       $result->tick,
                seed:       $result->seed,
                type:       EventType::FORK_TRIGGERED,
                title:      sprintf('Timeline Fork triggered at tick %d (pressure: %.2f)', $result->tick, $result->totalAnomalyScore()),
                severity:   Severity::CRITICAL,
                payload:    ['anomaly_score' => $result->totalAnomalyScore()]
            );
        }

        // 3. Transcendence threshold reached
        $transcendence = $result->nextStateVector->get(StateVector::DIMENSION_TRANSCENDENCE);
        if ($transcendence >= self::TRANSCENDENCE_THRESHOLD) {
            $events[] = ChronicleEvent::record(
                universeId: $universeId,
                tick:       $result->tick,
                seed:       $result->seed,
                type:       EventType::TRANSCENDENCE,
                title:      sprintf('Transcendence reached %.0f%%', $transcendence * 100),
                severity:   Severity::HIGH,
                payload:    ['transcendence' => $transcendence]
            );
        }

        // 4. Collapse imminent (entropy critical)
        $entropy = $result->nextStateVector->get(StateVector::DIMENSION_ENTROPY);
        if ($entropy >= self::COLLAPSE_ENTROPY_THRESHOLD) {
            $events[] = ChronicleEvent::record(
                universeId: $universeId,
                tick:       $result->tick,
                seed:       $result->seed,
                type:       EventType::COLLAPSE,
                title:      sprintf('Entropy critical at %.0f%% — Collapse imminent', $entropy * 100),
                severity:   Severity::CRITICAL,
                payload:    ['entropy' => $entropy, 'stability' => $result->nextStateVector->get(StateVector::DIMENSION_STABILITY)]
            );
        }

        return $events;
    }
}
