<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Arc\Service;

use InvalidArgumentException;
use WorldOS\Saga\Domain\Arc\ValueObject\Arc;
use WorldOS\Saga\Domain\Arc\ValueObject\ArcType;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * ArcPlanner — Domain Service scanning a timeline of StateVectors to segment them into Arcs.
 *
 * It uses a heuristic based on the first derivative (slope) and second derivative (convexity)
 * of specific dimensions (Cosmic Tension, Entropy, Stability).
 */
final class ArcPlanner
{
    /**
     * Segments a contiguous array of StateVectors into a sequence of Arcs.
     * The algorithm scans for inflection points in the narrative pressure (tension).
     *
     * @param array<int, StateVector> $timeline Array of StateVectors, inherently ordered by tick.
     * @return Arc[] Array of contiguous Arcs covering the entire input timeline.
     */
    public function plan(array $timeline): array
    {
        if (empty($timeline)) {
            return [];
        }

        $arcs = [];
        $ticks = array_keys($timeline);
        sort($ticks);

        $currentArcStart = $ticks[0];
        $lastTension = $timeline[$currentArcStart]->get(StateVector::DIMENSION_COSMIC_TENSION);
        $currentType = ArcType::INCITING;

        $n = count($ticks);

        for ($i = 1; $i < $n; $i++) {
            $tick = $ticks[$i];
            $state = $timeline[$tick];

            $tension = $state->get(StateVector::DIMENSION_COSMIC_TENSION);
            $entropy = $state->get(StateVector::DIMENSION_ENTROPY);
            
            $deltaTension = $tension - $lastTension;
            $arcLength = $tick - $currentArcStart;

            // Simple Narrative Heuristic Engine
            $nextType = $currentType;
            $shouldSplit = false;

            if ($currentType === ArcType::INCITING) {
                // If tension starts climbing consistently or a sharp spike occurs
                if ($deltaTension > 0.05 || $tension > 0.4) {
                    $nextType = ArcType::ESCALATION;
                    $shouldSplit = true;
                }
            } elseif ($currentType === ArcType::ESCALATION) {
                // Sudden chaos implies a crisis
                if ($entropy > 0.7 && $deltaTension > 0.1) {
                    $nextType = ArcType::CRISIS;
                    $shouldSplit = true;
                } elseif ($tension > 0.8) {
                    // Gradual climb hitting high tension
                    $nextType = ArcType::CLIMAX;
                    $shouldSplit = true;
                }
            } elseif ($currentType === ArcType::CRISIS) {
                // Crisis usually transitions quickly into Climax when tension peaks
                if ($tension > 0.85 || $arcLength > 5) { // Force transition if crisis dragged on
                    $nextType = ArcType::CLIMAX;
                    $shouldSplit = true;
                }
            } elseif ($currentType === ArcType::CLIMAX) {
                // When tension breaks and starts falling significantly
                if ($deltaTension < -0.1) {
                    $nextType = ArcType::RESOLUTION;
                    $shouldSplit = true;
                }
            } elseif ($currentType === ArcType::RESOLUTION) {
                // If resolution drags on and stabilizes, start a new inciting incident
                if ($arcLength > 10 && $tension < 0.3) {
                    $nextType = ArcType::INCITING;
                    $shouldSplit = true;
                }
            }

            // Create Arc if split threshold triggered
            if ($shouldSplit) {
                $startTension = $timeline[$currentArcStart]->get(StateVector::DIMENSION_COSMIC_TENSION);
                $arcs[] = Arc::create(
                    type: $currentType,
                    startTick: $currentArcStart,
                    endTick: $tick,
                    tensionDelta: $tension - $startTension
                );

                $currentArcStart = $tick;
                $currentType = $nextType;
            }

            $lastTension = $tension;
        }

        // Close the final dangling Arc
        $finalTick = end($ticks);
        if ($currentArcStart < $finalTick) {
            $startTension = $timeline[$currentArcStart]->get(StateVector::DIMENSION_COSMIC_TENSION);
            $finalTension = $timeline[$finalTick]->get(StateVector::DIMENSION_COSMIC_TENSION);
            $arcs[] = Arc::create(
                type: $currentType,
                startTick: $currentArcStart,
                endTick: $finalTick,
                tensionDelta: $finalTension - $startTension
            );
        }

        return $arcs;
    }
}
