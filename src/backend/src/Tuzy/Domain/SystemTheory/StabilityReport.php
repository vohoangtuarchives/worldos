<?php

declare(strict_types=1);

namespace Tuzy\Domain\SystemTheory;

/**
 * Simple DTO representing the result of a stability analysis.
 */
class StabilityReport
{
    public function __construct(
        public readonly float $maxAbsoluteEigenvalue
    ) {}

    /**
     * In a discrete-time system x(t+1) = f(x(t)), 
     * stability requires all eigenvalues |lambda| < 1.
     * If |lambda| > 1, the system is locally unstable and may undergo a bifurcation.
     */
    public function isStable(): bool
    {
        return $this->maxAbsoluteEigenvalue < 1.0;
    }
    
    public function isUnstable(): bool
    {
        return $this->maxAbsoluteEigenvalue >= 1.0;
    }
}
