<?php

declare(strict_types=1);

namespace Tuzy\Domain\SystemTheory;

use Tuzy\Domain\Math\Vector;
use Tuzy\Domain\Math\JacobianCalculator;
use Tuzy\Domain\Math\EigenSolver;
use Closure;

/**
 * Analyzes the local stability of the dynamical system around a specific state.
 */
class StabilityAnalyzer
{
    private JacobianCalculator $jacobianCalculator;
    private EigenSolver $eigenSolver;

    public function __construct(
        JacobianCalculator $jacobianCalculator = null, 
        EigenSolver $eigenSolver = null
    ) {
        $this->jacobianCalculator = $jacobianCalculator ?? new JacobianCalculator();
        $this->eigenSolver = $eigenSolver ?? new EigenSolver();
    }

    /**
     * Analyzes stability by evaluating the eigenvalues of the Jacobian matrix of f(X).
     * 
     * @param Closure $evolutionEquation f(X) -> Vector
     * @param Vector $currentState
     * @return StabilityReport
     */
    public function analyze(Closure $evolutionEquation, Vector $currentState): StabilityReport
    {
        $jacobian = $this->jacobianCalculator->compute($evolutionEquation, $currentState);
        $maxEigenvalue = $this->eigenSolver->maxAbsEigenvalue($jacobian);
        
        return new StabilityReport($maxEigenvalue);
    }
}
