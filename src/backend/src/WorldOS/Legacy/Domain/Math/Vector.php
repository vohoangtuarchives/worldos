<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Math;

use InvalidArgumentException;
use Closure;

/**
 * Pure Mathematical Vector.
 */
class Vector
{
    /** @var float[] */
    protected array $components;

    /**
     * @param float[] $components
     */
    public function __construct(array $components)
    {
        // Enforce all components are floats
        $this->components = array_map(fn($v) => (float)$v, $components);
    }

    /**
     * @param string[]|int[] $dimensions Keys of the dimensions
     */
    public static function zero(array $dimensions): self
    {
        $components = [];
        foreach ($dimensions as $dim) {
            $components[$dim] = 0.0;
        }
        return new self($components);
    }

    /**
     * @param string[]|int[] $dimensions
     * @param float $value
     */
    public static function fill(array $dimensions, float $value): self
    {
        $components = [];
        foreach ($dimensions as $dim) {
            $components[$dim] = $value;
        }
        return new self($components);
    }

    public function get(string|int $dimension): float
    {
        return $this->components[$dimension] ?? 0.0;
    }

    public function getAll(): array
    {
        return $this->components;
    }
    
    public function dimensionCount(): int
    {
        return count($this->components);
    }
    
    public function dimensions(): array
    {
        return array_keys($this->components);
    }

    public function add(Vector $other): self
    {
        $newComponents = [];
        foreach ($this->components as $dim => $val) {
            $newComponents[$dim] = $val + $other->get($dim);
        }
        foreach ($other->getAll() as $dim => $val) {
            if (!isset($newComponents[$dim])) {
                $newComponents[$dim] = $val;
            }
        }
        return new self($newComponents);
    }

    public function subtract(Vector $other): self
    {
        $newComponents = [];
        foreach ($this->components as $dim => $val) {
            $newComponents[$dim] = $val - $other->get($dim);
        }
        foreach ($other->getAll() as $dim => $val) {
            if (!isset($newComponents[$dim])) {
                $newComponents[$dim] = -$val;
            }
        }
        return new self($newComponents);
    }

    public function scale(float $scalar): self
    {
        $newComponents = array_map(fn($val) => $val * $scalar, $this->components);
        return new self($newComponents);
    }

    public function multiply(float $scalar): self
    {
        return $this->scale($scalar);
    }

    public function dot(Vector $other): float
    {
        $sum = 0.0;
        foreach ($this->components as $dim => $val) {
            $sum += $val * $other->get($dim);
        }
        return $sum;
    }

    public function magnitude(): float
    {
        return sqrt($this->dot($this));
    }

    public function normalize(): self
    {
        $mag = $this->magnitude();
        if ($mag == 0) {
            return $this;
        }
        return $this->scale(1 / $mag);
    }

    public function distance(Vector $other): float
    {
        return $this->subtract($other)->magnitude();
    }
    
    public function clamp(float $min, float $max): self
    {
        $newComponents = array_map(
            fn($val) => max($min, min($max, $val)), 
            $this->components
        );
        return new self($newComponents);
    }

    public function map(Closure $callback): self
    {
        $newComponents = array_map($callback, $this->components);
        return new self($newComponents);
    }
}
