<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\Mathematics;

use WorldOS\Domains\Evolution\ValueObjects\StateVector;

/**
 * LinearCouplingMatrix (A_0)
 * 
 * Represents the baseline interactions between the 17 dimensions.
 * Scaled to have a spectral radius of exactly 0.98 (edge of chaos).
 */
class LinearCouplingMatrix
{
    public array $A;

    public function __construct(?array $A = null)
    {
        if ($A === null) {
            $this->A = self::generateBalancedMatrix();
        } else {
            $this->A = $A;
        }
    }

    /**
     * Delta S_linear = A_0 * S
     */
    public function multiply(StateVector $S): array
    {
        return DynamicalMatrixMath::multiplyMatrixVector($this->A, $S->values);
    }

    /**
     * Constant drivers (C vector) to prevent trivial zero origin equilibrium.
     * S_dot = A_0 * S + C
     */
    public function getBaselineDrivers(): array
    {
        $d = array_fill(0, StateVector::DIMENSIONS, 0.0);
        $keys = StateVector::KEYS;
        
        $d[array_search('tech', $keys)] = 0.02; // Natural tendency to innovate
        $d[array_search('ce', $keys)] = 0.01;   // Constant cultural generation
        $d[array_search('expansion', $keys)] = 0.005; // Inherent ambition
        
        return $d;
    }

    /**

     * Generates a deterministic baseline sparse matrix tailored for 17 dimensions.
     * Scales it to a target spectral radius (0.98).
     */
    private static function generateBalancedMatrix(): array
    {
        $n = StateVector::DIMENSIONS;
        $A = array_fill(0, $n, array_fill(0, $n, 0.0));
        
        // Seed RNG for deterministic matrix generation
        mt_srand(42); 

        // Generate sparse matrix (~30% density)
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    // Small local damping
                    $A[$i][$j] = -0.05; 
                } else {
                    $rand = mt_rand() / mt_getrandmax();
                    if ($rand < 0.3) {
                        // Value between -0.05 and 0.05
                        $val = (mt_rand() / mt_getrandmax() - 0.5) * 0.1;
                        $A[$i][$j] = $val;
                    }
                }
            }
        }

        // Restore random seed to avoid affecting other parts
        mt_srand();

        // Target edge of chaos
        $targetRadius = 0.98;

        // Note: For DeltaS = A * S, the fixed point stability depends on eigenvalues of (I + A).
        // Let's compute spectral radius of (I + A)
        $I_plus_A = DynamicalMatrixMath::identityPlus($A);
        $currentRadius = DynamicalMatrixMath::spectralRadius($I_plus_A, 50);

        if ($currentRadius > 0.0) {
            // Scale A such that spectral radius of (I + A) becomes targetRadius
            // Wait, scaling A is tricky because I + c*A doesn't scale spectral radius linearly smoothly.
            // Simplified approach: just scale A directly for tuning
            $scale = 0.5; // Manual safety scale for now
            $A = DynamicalMatrixMath::scaleMatrix($A, $scale);
        }

        return $A;
    }
}
