<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Domain\Engine\ValueObject;

use InvalidArgumentException;

/**
 * Represents the N-dimensional physical state of a Universe at a specific tick.
 * Immutable. Each evolution produces a NEW StateVector.
 */
final class StateVector
{
    // Standard 17D dimension keys
    public const DIMENSION_ENTROPY          = 'entropy';
    public const DIMENSION_STABILITY        = 'stability';
    public const DIMENSION_POWER_DENSITY    = 'power_density';
    public const DIMENSION_RICHNESS         = 'cultural_richness';
    public const DIMENSION_ANOMALY          = 'anomaly_index';
    public const DIMENSION_TECH_CEILING     = 'tech_ceiling';
    public const DIMENSION_MAGIC_DENSITY    = 'magic_density';
    public const DIMENSION_FACTION_COUNT    = 'faction_count';
    public const DIMENSION_COSMIC_TENSION   = 'cosmic_tension';
    public const DIMENSION_LAW_ELASTICITY   = 'law_elasticity';
    public const DIMENSION_ERA_PRESSURE     = 'era_pressure';
    public const DIMENSION_RESILIENCE       = 'resilience';
    public const DIMENSION_MEMORY_DEPTH     = 'memory_depth';
    public const DIMENSION_CHAOS_SATURATION = 'chaos_saturation';
    public const DIMENSION_TRANSCENDENCE    = 'transcendence';
    public const DIMENSION_DARK_MATTER      = 'dark_matter';
    public const DIMENSION_SINGULARITY      = 'singularity';

    public const DEFAULT_DIMENSIONS = [
        self::DIMENSION_ENTROPY          => 0.0,
        self::DIMENSION_STABILITY        => 1.0,
        self::DIMENSION_POWER_DENSITY    => 0.5,
        self::DIMENSION_RICHNESS         => 0.3,
        self::DIMENSION_ANOMALY          => 0.0,
        self::DIMENSION_TECH_CEILING     => 0.2,
        self::DIMENSION_MAGIC_DENSITY    => 0.1,
        self::DIMENSION_FACTION_COUNT    => 0.3,
        self::DIMENSION_COSMIC_TENSION   => 0.1,
        self::DIMENSION_LAW_ELASTICITY   => 0.5,
        self::DIMENSION_ERA_PRESSURE     => 0.0,
        self::DIMENSION_RESILIENCE       => 0.8,
        self::DIMENSION_MEMORY_DEPTH     => 0.0,
        self::DIMENSION_CHAOS_SATURATION => 0.0,
        self::DIMENSION_TRANSCENDENCE    => 0.0,
        self::DIMENSION_DARK_MATTER      => 0.05,
        self::DIMENSION_SINGULARITY      => 0.0,
    ];

    private function __construct(
        private readonly array $dimensions
    ) {
    }

    public static function genesis(): self
    {
        return new self(self::DEFAULT_DIMENSIONS);
    }

    public static function fromArray(array $dimensions): self
    {
        foreach ($dimensions as $key => $value) {
            if (!is_float($value) && !is_int($value)) {
                throw new InvalidArgumentException("StateVector dimension [{$key}] must be numeric.");
            }
        }
        return new self(array_map(fn($v) => (float) $v, $dimensions));
    }

    /**
     * Returns a new StateVector with a single dimension modified.
     */
    public function withDimension(string $key, float $value): self
    {
        $dims = $this->dimensions;
        $dims[$key] = $value;
        return new self($dims);
    }

    public function get(string $key): float
    {
        return $this->dimensions[$key] ?? 0.0;
    }

    public function all(): array
    {
        return $this->dimensions;
    }

    public function toArray(): array
    {
        return $this->dimensions;
    }

    /**
     * Returns dimensions as an ordered float[] using the DEFAULT_DIMENSIONS key order.
     * Required for matrix multiplication where column order must be stable.
     *
     * @return float[]
     */
    public function toIndexedArray(): array
    {
        $keys   = array_keys(self::DEFAULT_DIMENSIONS);
        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->dimensions[$key] ?? 0.0;
        }
        return $result;
    }

    /**
     * Element-wise addition. Both vectors must share the same key set.
     */
    public function add(self $other): self
    {
        $dims = $this->dimensions;
        foreach ($other->dimensions as $key => $value) {
            $dims[$key] = ($dims[$key] ?? 0.0) + $value;
        }
        return new self($dims);
    }

    /**
     * Scalar multiplication (scale all dimensions by factor).
     */
    public function scale(float $factor): self
    {
        return new self(array_map(fn(float $v) => $v * $factor, $this->dimensions));
    }

    /**
     * Clamp all dimension values to [min, max].
     */
    public function clamp(float $min, float $max): self
    {
        return new self(array_map(
            fn(float $v) => max($min, min($max, $v)),
            $this->dimensions
        ));
    }

    /**
     * L2 norm — used by SpectralAnalyzer power iteration.
     */
    public function norm(): float
    {
        $sum = 0.0;
        foreach ($this->dimensions as $v) {
            $sum += $v * $v;
        }
        return sqrt($sum);
    }
}
