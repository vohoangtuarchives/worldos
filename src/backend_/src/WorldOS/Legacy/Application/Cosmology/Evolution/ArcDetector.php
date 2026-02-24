<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\Evolution;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * Rule-based arc phase detection from current state (and optional recent ticks).
 * Deterministic; no Markov yet.
 */
final class ArcDetector
{
    public function detect(WorldStateVector $state, ?array $recentTicks = null): ArcPhase
    {
        $e = $state->getEntropy();
        $l = $state->getLegitimacy();
        $i = $state->getInnovation();
        $c = $state->getCohesion();
        $order = $state->getOrder();

        if ($e > 0.75 && $l < 0.3) {
            return ArcPhase::COLLAPSE;
        }
        if ($e > 0.6 && $c < 0.4) {
            return ArcPhase::CRISIS;
        }
        if ($i > 0.7 && $e < 0.4) {
            return ArcPhase::GOLDEN_AGE;
        }
        if ($i < 0.3 && $e > 0.5) {
            return ArcPhase::STAGNATION;
        }
        if ($order > 0.7 && $c > 0.6 && $e < 0.4) {
            return ArcPhase::EXPANSION;
        }
        if ($e < 0.3 && $order > 0.5 && $l > 0.5) {
            return $order > 0.6 ? ArcPhase::REFORMATION : ArcPhase::GENESIS;
        }

        return ArcPhase::EXPANSION;
    }
}
