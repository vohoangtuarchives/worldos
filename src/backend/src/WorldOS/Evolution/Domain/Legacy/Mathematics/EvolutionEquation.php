<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\Mathematics;

use WorldOS\Legacy\Domain\Math\Vector;
use WorldOS\Legacy\Domain\Math\EvolutionEquationInterface;
use WorldOS\Evolution\Domain\Legacy\ValueObject\StateVector;

/**
 * Concrete Master Equation for Civilization Evolution.
 * X_{t+1} = saturate(X_t + dt * F(X))
 */
class EvolutionEquation implements EvolutionEquationInterface
{
    public function __construct(
        private LinearCouplingMatrix $linear,
        private QuadraticInteraction $quadratic,
        private EntropyDestabilizer $entropy,
        private AttractorField $attractor,
        private PressureAccumulationField $pressure,
        private InnovationBurst $innovation
    ) {}

    /**
     * Computes dx/dt = A*X + Q(X) + E(X) + Att(X) + P(X) + I(X)
     */
    public function computeDerivative(Vector $state): Vector
    {
        // Wrap into StateVector if not already, to leverage domain structure if needed
        $s = ($state instanceof StateVector) ? $state : new StateVector($state->getAll());

        // Extract forces as mapped Vectors
        // Note: For now we convert legacy array outputs to Vectors assuming domain returns array
        $fLinear = new Vector($this->linear->multiply($s));
        $fQuadratic = new Vector($this->quadratic->apply($s->values));
        $fEntropy = new Vector($this->entropy->apply($s->values));
        $fAttractor = new Vector($this->attractor->apply($s->values, null)); // Placeholder logic for attractor field target
        $fPressure = new Vector($this->pressure->apply($s->values, null)); // Placeholder logic for external pressure
        $fInnovation = new Vector($this->innovation->apply($s->values, 0.5)); // Base probability

        $baseForce = $fLinear
            ->add($fQuadratic)
            ->add($fEntropy)
            ->add($fAttractor)
            ->add($fPressure)
            ->add($fInnovation);

        return $baseForce;
    }

    public function nextState(Vector $state, float $dt): Vector
    {
        $derivative = $this->computeDerivative($state);
        
        // Euler discretization: X_{t+1} = X_t + dt * F(X_t)
        $nextRaw = $state->add($derivative->scale($dt));
        
        // Passing it through StateVector saturates it using tanh
        return new StateVector($nextRaw->getAll());
    }
}
