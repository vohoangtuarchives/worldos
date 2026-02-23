<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\Service;

use WorldOS\Simulation\Domain\Engine\ValueObject\AnomalyEvent;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * AnomalyDetector: Domain Service.
 * Detects when any dimension in a StateVector crosses a configured critical threshold.
 * Used externally for explicit anomaly checks (e.g., event replay audits).
 */
final class AnomalyDetector
{
    /**
     * @param array<string, float> $criticalThresholds Keyed by dimension name (from PhysicsCore constraints)
     * @return AnomalyEvent[]
     */
    public function detect(StateVector $state, array $criticalThresholds): array
    {
        $anomalies = [];

        foreach ($criticalThresholds as $dimension => $threshold) {
            $value = $state->get($dimension);

            if ($value >= $threshold) {
                // Normalized intensity: 0.0 at threshold, 1.0 at theoretical max
                $intensity   = min(1.0, ($value - $threshold) / (1.0 - $threshold + 1e-9));
                $anomalies[] = new AnomalyEvent($dimension, $value, $threshold, $intensity);
            }
        }

        return $anomalies;
    }

    /**
     * Quick check: is any dimension in an anomalous state?
     *
     * @param array<string, float> $criticalThresholds
     */
    public function hasAnomaly(StateVector $state, array $criticalThresholds): bool
    {
        foreach ($criticalThresholds as $dimension => $threshold) {
            if ($state->get($dimension) >= $threshold) {
                return true;
            }
        }
        return false;
    }
}
