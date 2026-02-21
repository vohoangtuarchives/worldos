<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Mathematics;

/**
 * Dominance inertia: switch dominant only when another attractor exceeds current + margin
 * for N consecutive cycles. Configurable via constructor.
 */
class InertiaPolicy
{
    public function __construct(
        private float $dominanceMargin = 0.1,
        private int $inertiaCycles = 20
    ) {
    }

    /**
     * Resolve new dominant given current influences and previous state.
     *
     * @param array<string, float> $influences attractor name => influence
     * @return array{dominant: string, consecutive_cycles: int}
     */
    public function resolveDominant(
        ?string $currentDominant,
        array $influences,
        int $consecutiveCyclesWithCandidateLeading = 0
    ): array {
        if (empty($influences)) {
            return ['dominant' => $currentDominant ?? '', 'consecutive_cycles' => 0];
        }

        arsort($influences, SORT_NUMERIC);
        $candidate = array_key_first($influences);
        $candidateInfluence = $influences[$candidate];

        if ($currentDominant === null || $currentDominant === '') {
            return ['dominant' => $candidate, 'consecutive_cycles' => 0];
        }

        if ($candidate === $currentDominant) {
            return ['dominant' => $currentDominant, 'consecutive_cycles' => 0];
        }

        $currentInfluence = $influences[$currentDominant] ?? 0.0;
        if ($candidateInfluence <= $currentInfluence + $this->dominanceMargin) {
            return ['dominant' => $currentDominant, 'consecutive_cycles' => 0];
        }

        $nextConsecutive = $consecutiveCyclesWithCandidateLeading + 1;
        if ($nextConsecutive >= $this->inertiaCycles) {
            return ['dominant' => $candidate, 'consecutive_cycles' => 0];
        }

        return ['dominant' => $currentDominant, 'consecutive_cycles' => $nextConsecutive];
    }
}


