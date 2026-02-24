<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Math;

/**
 * Solves for the dominant eigenvalue using the Power Iteration method.
 */
class EigenSolver
{
    private int $maxIterations;
    private float $tolerance;

    public function __construct(int $maxIterations = 1000, float $tolerance = 1e-6)
    {
        $this->maxIterations = $maxIterations;
        $this->tolerance = $tolerance;
    }

    /**
     * Compute the absolute dominant eigenvalue max(|lambda|).
     */
    public function maxAbsEigenvalue(Matrix $J): float
    {
        $dims = $J->getColDimensions();
        if (empty($dims)) {
            return 0.0;
        }

        // Initialize with a random normalized vector
        $randData = [];
        foreach ($dims as $dim) {
            $randData[$dim] = mt_rand() / mt_getrandmax();
        }
        $v = (new Vector($randData))->normalize();

        $lambda = 0.0;
        for ($i = 0; $i < $this->maxIterations; $i++) {
            $vNext = $J->multiplyVector($v);
            $vNextMag = $vNext->magnitude();

            if ($vNextMag < 1e-12) {
                return 0.0; // Converged to 0
            }

            // Rayleigh quotient: vT * J * v / (vT * v)
            // since v is normalized, vT * v = 1
            $lambdaNext = $vNext->dot($v);
            
            $v = $vNext->normalize();

            // Check convergence
            if (abs($lambdaNext - $lambda) < $this->tolerance) {
                return abs($lambdaNext);
            }

            $lambda = $lambdaNext;
        }

        return abs($lambda);
    }
}
