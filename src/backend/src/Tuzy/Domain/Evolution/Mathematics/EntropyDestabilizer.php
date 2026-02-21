<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Mathematics;

use Tuzy\Domain\Evolution\ValueObject\StateVector;

/**
 * EntropyDestabilizer (D)
 * 
 * Injecting Cosmic Entropy into the system.
 * Designed as a rank-1 perturbation matrix D = gamma * v * w^T
 */
class EntropyDestabilizer
{
    public array $v; // Collapse vector (Where does the system fall)
    public array $w; // Projection vector (Which dimensions trigger the fall)
    public float $gamma; // Base sensitivity

    public function __construct()
    {
        // Gamma 0.035: competitive balance with LinearCouplingMatrix ie diagonal -0.065
        // Entropy equilibrium handled by diagonal damping + AttractorFieldModifier
        $this->gamma = 0.035;
        $this->v = array_fill(0, StateVector::DIMENSIONS, 0.0);
        $this->w = array_fill(0, StateVector::DIMENSIONS, 0.0);

        // Map indices
        $keys = StateVector::KEYS;

        // V vector (What happens when entropy spikes)
        // Stability falls, Inequality rises, Entropy rises, Legitimacy falls, Sustainability falls
        $this->v[array_search('stability', $keys)] = -0.6;
        $this->v[array_search('legitimacy', $keys)] = -0.5;
        $this->v[array_search('sustainability', $keys)] = -0.4;
        $this->v[array_search('ie', $keys)] = 0.35; // reduced from 0.50: softer self-amplification
        $this->v[array_search('inequality', $keys)] = 0.5;

        // W vector (What triggers the entropy perturbation)
        // High prosperity, high tech, high inequality amplify the vulnerability
        $this->w[array_search('tech', $keys)] = 0.3;
        $this->w[array_search('prosperity', $keys)] = 0.3;
        $this->w[array_search('inequality', $keys)] = 0.4;
        $this->w[array_search('ie', $keys)] = 0.5;
    }

    /**
     * D = E * gamma * v * (w^T * S)
     */
    public function apply(StateVector $S, float $entropy, float $elasticity = 0.0): array
    {
        // Elasticity protects the system by reducing effective gamma
        $gammaEff = $this->gamma * (1.0 - $elasticity);
        if ($gammaEff < 0) $gammaEff = 0;

        // Projection: w^T * S
        $projection = DynamicalMatrixMath::dotProduct($this->w, $S->values);

        $n = count($this->v);
        $result = array_fill(0, $n, 0.0);

        // Delta S = E * gammaEff * projection * v
        for ($i = 0; $i < $n; $i++) {
            $result[$i] = $entropy * $gammaEff * $projection * $this->v[$i];
        }

        return $result;
    }
}
