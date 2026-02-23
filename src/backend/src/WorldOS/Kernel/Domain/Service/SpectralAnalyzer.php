<?php

declare(strict_types=1);

namespace WorldOS\Kernel\Domain\Service;

use WorldOS\Kernel\Domain\ValueObject\CouplingMatrix;

/**
 * SpectralAnalyzer — Domain Service for kernel stability analysis.
 *
 * Validates that a CouplingMatrix keeps the simulation within bounded evolution:
 *   ρ(A) ≤ 1.0 → system converges or stays bounded
 *   ρ(A) > 1.0 → diverges (chaos runaway — rejected)
 *
 * "Edge of chaos" target: ρ(A) ≈ 0.95–0.99
 *
 * Stateless. Can be used as a direct call or injected as a service.
 */
final class SpectralAnalyzer
{
    /**
     * Compute approximate spectral radius of the given matrix.
     * Delegates to CouplingMatrix power iteration (100 steps).
     */
    public function computeSpectralRadius(CouplingMatrix $matrix): float
    {
        return $matrix->getSpectralRadius();
    }

    /**
     * Returns true if ρ(A) ≤ $threshold (default: 1.0 = bounded evolution).
     */
    public function isStable(CouplingMatrix $matrix, float $threshold = 1.0): bool
    {
        return $this->computeSpectralRadius($matrix) <= $threshold;
    }

    /**
     * Returns true if matrix is in the "edge of chaos" zone.
     * Default zone: 0.90 ≤ ρ(A) ≤ 0.99.
     */
    public function isEdgeOfChaos(
        CouplingMatrix $matrix,
        float $lowerBound = 0.90,
        float $upperBound = 0.99
    ): bool {
        $rho = $this->computeSpectralRadius($matrix);
        return $rho >= $lowerBound && $rho <= $upperBound;
    }

    /**
     * Generate a random stable CouplingMatrix with ρ(A) ≈ $targetRadius.
     *
     * The matrix is physically meaningful:
     *  - Dominant diagonal ensures each dimension self-regresses
     *  - Small off-diagonal entries model cross-dimension influence
     *
     * @param int   $dimensions  Must be 17 for V5
     * @param float $targetRadius Target spectral radius (0.0, 1.0]
     * @param int   $seed         For determinism
     */
    public function generateStableMatrix(
        int   $dimensions,
        float $targetRadius = 0.98,
        int   $seed = 42
    ): CouplingMatrix {
        if ($dimensions !== 17) {
            throw new \InvalidArgumentException(
                "V5 kernel requires 17 dimensions, {$dimensions} given."
            );
        }

        if ($targetRadius <= 0.0 || $targetRadius > 1.0) {
            throw new \InvalidArgumentException(
                "targetRadius must be in range (0.0, 1.0], {$targetRadius} given."
            );
        }

        return CouplingMatrix::generateStable($targetRadius, $seed);
    }

    /**
     * Validate a CouplingMatrix and throw if it would cause divergence.
     *
     * @throws \DomainException if ρ(A) > 1.0
     */
    public function assertStable(CouplingMatrix $matrix): void
    {
        $rho = $this->computeSpectralRadius($matrix);

        if ($rho > 1.0) {
            throw new \DomainException(
                sprintf(
                    'CouplingMatrix is unstable: ρ(A) = %.4f > 1.0. '
                    . 'Simulation would diverge. Reduce off-diagonal coupling or scale.',
                    $rho
                )
            );
        }
    }
}
