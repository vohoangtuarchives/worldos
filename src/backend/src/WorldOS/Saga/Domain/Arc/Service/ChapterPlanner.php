<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Arc\Service;

use WorldOS\Saga\Domain\Arc\ValueObject\Arc;
use WorldOS\Saga\Domain\Arc\ValueObject\ChapterNode;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * ChapterPlanner — Analyzes StateVectors within an Arc to produce individual chapter beats.
 * Follows the "show, don't tell" mathematical mapping:
 * A chapter focuses mechanically on the dimension undergoing the most volatile change.
 */
final class ChapterPlanner
{
    /**
     * Generates a sequence of ChapterNodes for a given Arc.
     * Finds the maximum delta across all 17 dimensions to assign a thematic focus.
     *
     * @param Arc $arc
     * @param array<int, StateVector> $arcTimeline Must cover at least startTick to endTick of the Arc.
     * @return ChapterNode[]
     */
    public function plan(Arc $arc, array $arcTimeline): array
    {
        $chapters = [];
        
        $start = $arc->getStartTick();
        $end = $arc->getEndTick();

        if ($start >= $end || empty($arcTimeline)) {
            return [];
        }

        // We need at least the previous tick to calculate a delta.
        // If not available, we assume the first node is a setup node (intensity 0).
        $previousState = $arcTimeline[$start] ?? null;

        for ($tick = $start + 1; $tick <= $end; $tick++) {
            if (!isset($arcTimeline[$tick])) {
                continue; // Skip missing data points
            }

            $currentState = $arcTimeline[$tick];

            if ($previousState === null) {
                // Should not happen theoretically inside a contiguous loop, but fallback just in case
                $chapters[] = ChapterNode::create($tick, 0.0, StateVector::DIMENSION_ENTROPY);
                $previousState = $currentState;
                continue;
            }

            // Calculate deltas for all dimensions
            $maxDelta = 0.0;
            $dominantDim = StateVector::DIMENSION_ENTROPY; // default fallback

            foreach (array_keys(StateVector::DEFAULT_DIMENSIONS) as $dimKey) {
                $prevVal = $previousState->get($dimKey);
                $currVal = $currentState->get($dimKey);
                $delta = abs($currVal - $prevVal);

                if ($delta > $maxDelta) {
                    $maxDelta = $delta;
                    $dominantDim = $dimKey;
                }
            }

            // The intensity of the chapter is the raw maximum dimension shift
            $intensity = min(1.0, $maxDelta * 2.0); // Simple scaling to make shifts more readable

            $chapters[] = ChapterNode::create(
                tick: $tick,
                intensity: $intensity,
                dominantDimension: $dominantDim
            );

            $previousState = $currentState;
        }

        return $chapters;
    }
}
