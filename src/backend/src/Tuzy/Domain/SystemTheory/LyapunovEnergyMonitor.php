<?php

declare(strict_types=1);

namespace Tuzy\Domain\SystemTheory;

use Tuzy\Domain\Math\Vector;

/**
 * Monitors the total energy of the system V(X) = X^T X (Lyapunov function candidate).
 */
class LyapunovEnergyMonitor
{
    /**
     * Compute the energy V(X) = X \dot X
     */
    public function calculateEnergy(Vector $state): float
    {
        return $state->dot($state);
    }

    /**
     * Compute delta V = V(next) - V(prev).
     * If delta V < 0 on average, the system is dissipating energy and moving towards a stable fixed point.
     * If delta V > 0, the system is accumulating energy and may explode.
     */
    public function calculateDelta(Vector $previousState, Vector $nextState): float
    {
        return $this->calculateEnergy($nextState) - $this->calculateEnergy($previousState);
    }
}
