<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing the mathematical core and deterministic rules of the World Blueprint.
 */
final class PhysicsCore
{
    /**
     * @param int $dimensions Number of dimensions in the state space (e.g., 17D)
     * @param GeneVector $geneBounds The min/max boundaries for the dimensions
     * @param float $baselineSpectralRadius The target spectral radius around edge-of-chaos (e.g., 0.98)
     * @param float $chaosFactor Non-linear shock/chaos injection factor (e.g., 0.02)
     * @param array $constraints Custom invariant rules (e.g., ['energy + entropy <= 1.2'])
     */
    public function __construct(
        private readonly int $dimensions,
        private readonly GeneVector $geneBounds,
        private readonly float $baselineSpectralRadius = 0.98,
        private readonly float $chaosFactor = 0.02,
        private readonly array $constraints = []
    ) {
        if ($this->dimensions < 1) {
            throw new InvalidArgumentException("Dimensions must be at least 1.");
        }
        if ($this->baselineSpectralRadius < 0.0 || $this->baselineSpectralRadius > 1.0) {
            throw new InvalidArgumentException("Spectral radius must be between 0.0 and 1.0.");
        }
        if ($this->chaosFactor < 0.0 || $this->chaosFactor > 1.0) {
            throw new InvalidArgumentException("Chaos factor must be between 0.0 and 1.0.");
        }
    }

    public static function create(
        int $dimensions,
        GeneVector $geneBounds,
        float $baselineSpectralRadius = 0.98,
        float $chaosFactor = 0.02,
        array $constraints = []
    ): self {
        return new self($dimensions, $geneBounds, $baselineSpectralRadius, $chaosFactor, $constraints);
    }

    public function getDimensions(): int
    {
        return $this->dimensions;
    }

    public function getGeneBounds(): GeneVector
    {
        return $this->geneBounds;
    }

    public function getBaselineSpectralRadius(): float
    {
        return $this->baselineSpectralRadius;
    }

    public function getChaosFactor(): float
    {
        return $this->chaosFactor;
    }

    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function toArray(): array
    {
        return [
            'dimensions' => $this->dimensions,
            'gene_bounds' => $this->geneBounds->toArray(),
            'spectral_radius' => $this->baselineSpectralRadius,
            'chaos_factor' => $this->chaosFactor,
            'constraints' => $this->constraints,
        ];
    }
}
