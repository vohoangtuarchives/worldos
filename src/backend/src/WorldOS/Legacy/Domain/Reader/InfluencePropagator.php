<?php

namespace WorldOS\Legacy\Domain\Reader;

use WorldOS\Legacy\Application\Material\State\WorldState;

/**
 * InfluencePropagator - Convert reader influence into WorldState deltas
 * 
 * Applies dampening to prevent reader control.
 */
class InfluencePropagator
{
    /**
     * Dampening factor (readers influence, not control).
     */
    private const DAMPENING_FACTOR = 0.5;

    /**
     * Propagate reader influence to WorldState deltas.
     * 
     * @param array $aggregatedVotes Vote aggregation results
     * @param WorldState $currentState Current world state
     * @return array Deltas to apply
     */
    public function propagate(array $aggregatedVotes, WorldState $currentState): array
    {
        $deltas = [];

        foreach ($aggregatedVotes as $vote) {
            $rawDelta = $vote['delta'] ?? [];

            // Apply dampening
            foreach ($rawDelta as $field => $value) {
                $dampenedValue = $value * self::DAMPENING_FACTOR;
                $deltas[$field] = ($deltas[$field] ?? 0) + $dampenedValue;
            }
        }

        // Normalize deltas (ensure within bounds)
        return $this->normalizeDeltas($deltas);
    }

    /**
     * Normalize deltas to ensure they're within acceptable bounds.
     */
    private function normalizeDeltas(array $deltas): array
    {
        $normalized = [];

        foreach ($deltas as $field => $value) {
            // Clamp to ±0.3 per epoch (same as material law engine)
            $normalized[$field] = min(0.3, max(-0.3, $value));
        }

        return $normalized;
    }

    /**
     * Get dampening factor.
     */
    public function getDampeningFactor(): float
    {
        return self::DAMPENING_FACTOR;
    }
}
