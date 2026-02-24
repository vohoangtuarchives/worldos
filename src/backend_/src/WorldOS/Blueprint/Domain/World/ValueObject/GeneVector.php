<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

final class GeneVector
{
    /**
     * @param array<string, mixed> $traits Key traits of the genre/physics (e.g., 'tech_ceiling', 'magic_system')
     * @param array<string, array<string, array{0: float, 1: float}|float>> $dimensionBounds Multi-dimensional bounds
     */
    public function __construct(
        private readonly array $traits = [],
        private readonly array $dimensionBounds = []
    ) {
    }

    public static function create(array $traits, array $dimensionBounds): self
    {
        return new self($traits, $dimensionBounds);
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function getDimensionBounds(): array
    {
        return $this->dimensionBounds;
    }

    /**
     * Export raw arrays for persistence.
     */
    public function toArray(): array
    {
        return [
            'traits' => $this->traits,
            'dimension_bounds' => $this->dimensionBounds,
        ];
    }

    /**
     * Helper to extract legacy compatible gene_vector.
     */
    public function toLegacyArray(): array
    {
        $legacy = $this->traits;
        $legacy['seed_vector'] = $this->dimensionBounds;
        return $legacy;
    }
}
