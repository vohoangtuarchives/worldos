<?php

declare(strict_types=1);

namespace Tuzy\Domain\Math;

/**
 * Interface for any master dynamical equation defining dx/dt = F(X).
 */
interface EvolutionEquationInterface
{
    /**
     * Compute the continuous derivative Vector (dx/dt) based on current state Vector.
     */
    public function computeDerivative(Vector $state): Vector;

    /**
     * Compute the discrete future state mapping (usually Euler approximation with step dt).
     */
    public function nextState(Vector $state, float $dt): Vector;
}
