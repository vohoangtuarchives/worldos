<?php

declare(strict_types=1);

namespace Tests\Unit\WorldOS\Simulation;

use PHPUnit\Framework\TestCase;
use WorldOS\Simulation\Domain\Engine\Service\AnomalyDetector;
use WorldOS\Simulation\Domain\Engine\Service\ForkDecider;
use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\TickResult;

class AnomalyAndForkTest extends TestCase
{
    // --- AnomalyDetector ---

    public function test_anomaly_detector_identifies_threshold_breach()
    {
        $detector = new AnomalyDetector();

        $state = StateVector::genesis()->withDimension(StateVector::DIMENSION_ENTROPY, 0.9);
        $thresholds = [StateVector::DIMENSION_ENTROPY => 0.75];

        $anomalies = $detector->detect($state, $thresholds);

        $this->assertCount(1, $anomalies);
        $this->assertEquals(StateVector::DIMENSION_ENTROPY, $anomalies[0]->dimension);
        $this->assertGreaterThan(0.0, $anomalies[0]->intensity);
    }

    public function test_anomaly_detector_returns_empty_when_below_threshold()
    {
        $detector = new AnomalyDetector();

        $state = StateVector::genesis()->withDimension(StateVector::DIMENSION_ENTROPY, 0.3);
        $thresholds = [StateVector::DIMENSION_ENTROPY => 0.75];

        $anomalies = $detector->detect($state, $thresholds);

        $this->assertEmpty($anomalies);
    }

    // --- ForkDecider ---

    public function test_fork_decider_triggers_above_threshold()
    {
        $decider = new ForkDecider(forkThreshold: 0.5);

        // Simulate a TickResult with total anomaly score = 0.8 (2 anomalies of 0.4 each)
        $anomalies = [
            new AnomalyEvent(StateVector::DIMENSION_ENTROPY, 0.9, 0.75, 0.4),
            new AnomalyEvent(StateVector::DIMENSION_CHAOS_SATURATION, 0.85, 0.7, 0.4),
        ];

        $result = new TickResult(
            tick: 5,
            seed: 42,
            nextStateVector: StateVector::genesis(),
            entropyDelta: 0.1,
            existenceWeight: 0.6,
            anomalies: $anomalies
        );

        $this->assertTrue($decider->shouldFork($result));
        $this->assertGreaterThanOrEqual(1.0, $decider->forkPressure($result));
    }

    public function test_fork_decider_does_not_trigger_below_threshold()
    {
        $decider = new ForkDecider(forkThreshold: 0.75);

        $anomalies = [
            new AnomalyEvent(StateVector::DIMENSION_ENTROPY, 0.8, 0.75, 0.2),
        ];

        $result = new TickResult(
            tick: 3,
            seed: 42,
            nextStateVector: StateVector::genesis(),
            entropyDelta: 0.05,
            existenceWeight: 0.7,
            anomalies: $anomalies
        );

        $this->assertFalse($decider->shouldFork($result));
    }
}
