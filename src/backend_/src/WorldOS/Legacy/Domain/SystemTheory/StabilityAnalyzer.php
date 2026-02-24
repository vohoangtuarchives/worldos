<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\SystemTheory;

use WorldOS\Legacy\Domain\Math\Vector;
use WorldOS\Legacy\Domain\Math\JacobianCalculator;
use WorldOS\Legacy\Domain\Math\EigenSolver;
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
