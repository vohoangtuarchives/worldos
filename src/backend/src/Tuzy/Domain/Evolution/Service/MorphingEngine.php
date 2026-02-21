<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\Entity\Aggregates\AttractorAggregate;
use Tuzy\Domain\Evolution\ValueObject\AttractorIncarnation;
use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Illuminate\Support\Str;

/**
 * MorphingEngine handles oscillatory, smooth transitions between attractor states.
 * Instead of instant jumps, attractors morph over multiple ticks with damped oscillation.
 */
class MorphingEngine
{
    private const DAMPING_FACTOR = 0.85;
    private const OSCILLATION_FREQUENCY = 0.1; // radians per tick

    /**
     * Initiate a new morph by creating a child incarnation.
     */
    public function startMorph(
        AttractorAggregate $attractor,
        array $targetCentroid,
        float $intensity = 1.0
    ): AttractorIncarnation {
        $currentInc = $attractor->getCurrentIncarnation();

        if (!$currentInc) {
            throw new \RuntimeException("Cannot morph: No current incarnation found.");
        }

        // Close current incarnation
        // (In production, update $currentInc->endTick via repository)

        // Calculate rebirth gain
        $rebirthGain = $this->calculateRebirthGain($currentInc, $targetCentroid);

        // Create new incarnation
        $newInc = new AttractorIncarnation(
            id: (string) Str::uuid(),
            attractorId: $attractor->id,
            parentIncarnationId: $currentInc->id,
            startTick: ($currentInc->endTick ?? 0) + 1,
            endTick: null,
            centroidSnapshot: $currentInc->centroidSnapshot, // Will morph toward target
            semanticSnapshot: [],
            basinRadius: $currentInc->basinRadius,
            curvatureFactor: $currentInc->curvatureFactor,
            rebirthGainFromParent: $rebirthGain,
            morphIntensity: $intensity
        );

        return $newInc;
    }

    /**
     * Step the morph process: Apply oscillatory damping to gradually shift centroid.
     */
    public function stepMorph(
        AttractorIncarnation $incarnation,
        array $targetCentroid,
        int $elapsedTicks
    ): array {
        $current = $incarnation->centroidSnapshot;
        $delta = [];

        foreach ($targetCentroid as $key => $targetValue) {
            $currentValue = $current[$key] ?? 0.0;
            $diff = $targetValue - $currentValue;

            // Damped oscillatory formula
            // x(t) = target + (initial - target) * exp(-damping * t) * cos(omega * t)
            $dampFactor = exp(-self::DAMPING_FACTOR * $elapsedTicks * 0.01);
            $oscFactor = cos(self::OSCILLATION_FREQUENCY * $elapsedTicks);

            $newValue = $targetValue + ($diff * $dampFactor * $oscFactor);
            $delta[$key] = $newValue;
        }

        return $delta;
    }

    /**
     * Check if morph is complete (centroid has stabilized near target).
     */
    public function isMorphComplete(
        array $currentCentroid,
        array $targetCentroid,
        float $tolerance = 0.01
    ): bool {
        foreach ($targetCentroid as $key => $targetValue) {
            $currentValue = $currentCentroid[$key] ?? 0.0;
            if (abs($targetValue - $currentValue) > $tolerance) {
                return false;
            }
        }

        return true;
    }

    private function calculateRebirthGain(
        AttractorIncarnation $parent,
        array $targetCentroid
    ): float {
        // Simple heuristic: Distance between parent and target
        $distance = 0.0;
        foreach ($targetCentroid as $key => $value) {
            $parentValue = $parent->centroidSnapshot[$key] ?? 0.0;
            $distance += pow($value - $parentValue, 2);
        }

        return sqrt($distance);
    }
}



