<?php

declare(strict_types=1);

namespace App\Modules\Simulation\Services\Kernel\Stability;

use App\Modules\Simulation\Matrix\MatrixOperator;
use App\Modules\Simulation\Services\Kernel\KernelMathException;

final class SpectralEstimator
{
    /**
     * @param MatrixOperator $J       Dynamic bounded operator matrix
     * @param int            $iterations Limit iterations, default mapped for O(n^2) scaling
     * @return float Spectral Margin $\rho(\mathbf{J})$ Estimation.
     * @throws KernelMathException On absolute divergence.
     */
    public function estimate(MatrixOperator $J, int $iterations = 50): float
    {
        $n = $J->dimension();
        // Step 1: Initialize random vector b0
        $v = $this->randomVector($n);

        // Step 2: Iterate to approximate dominant eigenvector v_k
        for ($k = 0; $k < $iterations; $k++) {
            $v = $J->multiplyVector($v);
            $norm = $this->norm($v);

            if ($norm === 0.0) {
                // Return immediate 0.0 margin scaling. System has crashed into the void.
                return 0.0;
            }

            // Normalize vector to stop floating precision explode
            $v = $this->normalize($v, $norm);
        }

        // Final application. Multiply one last time to assess bound: $\rho \approx \|Jw\| / \|w\|$ (given w is normalized)
        $w = $J->multiplyVector($v);

        return $this->norm($w);
    }

    /**
     * Helper: Gen array of pure structural random normalized inputs.
     * Use determinism PRNG injected in production, but bounded rand is acceptable here for approximation
     */
    private function randomVector(int $size): array
    {
        $v = [];
        for ($i = 0; $i < $size; $i++) {
            $v[] = (float) mt_rand() / mt_getrandmax();
        }
        return $v;
    }

    private function norm(array $v): float
    {
        $sumSq = 0.0;
        foreach ($v as $val) {
            $sumSq += $val * $val;
        }
        return sqrt($sumSq);
    }

    private function normalize(array $v, float $norm): array
    {
        $normalized = [];
        foreach ($v as $val) {
            $normalized[] = $val / $norm;
        }
        return $normalized;
    }
}
