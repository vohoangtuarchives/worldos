<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Mathematics;

use Tuzy\Domain\Evolution\ValueObject\WorldStateVector;
use Tuzy\Domain\Evolution\ValueObject\Attractor;

/**
 * Computes influence of each attractor on a state: influence = 1 / (distance + Îµ), then normalized.
 * Used for multi-attractor meta-history (dominance from influence ratio).
 */
class DominanceCalculator
{
    private const EPSILON = 0.01;

    /**
     * @param list<Attractor> $attractors
     * @return array<string, float> attractor name => influence (sum = 1.0)
     */
    public function influences(WorldStateVector $state, array $attractors): array
    {
        if (empty($attractors)) {
            return [];
        }

        $raw = [];
        foreach ($attractors as $attractor) {
            $d = $attractor->distanceTo($state);
            $raw[$attractor->getName()] = 1.0 / ($d + self::EPSILON);
        }

        $sum = array_sum($raw);
        if ($sum <= 0.0) {
            $n = count($raw);
            return array_map(fn () => 1.0 / $n, array_flip(array_keys($raw)));
        }

        $out = [];
        foreach ($raw as $name => $v) {
            $out[$name] = $v / $sum;
        }
        return $out;
    }

    /**
     * Name of attractor with highest influence (no inertia).
     */
    public function dominantAttractor(WorldStateVector $state, array $attractors): ?string
    {
        $inf = $this->influences($state, $attractors);
        if (empty($inf)) {
            return null;
        }
        arsort($inf, SORT_NUMERIC);
        return array_key_first($inf);
    }
}


