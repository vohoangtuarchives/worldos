<?php

namespace WorldOS\Legacy\Application\Cosmology\Mathematics;

use InvalidArgumentException;

class Vector
{
    protected array $components;

    public function __construct(array $components)
    {
        $this->components = $components;
    }

    public static function zero(array $dimensions): self
    {
        return new self(array_fill_keys($dimensions, 0.0));
    }

    public function get(string $dimension): float
    {
        return $this->components[$dimension] ?? 0.0;
    }

    public function getAll(): array
    {
        return $this->components;
    }

    public function add(Vector $other): self
    {
        $newComponents = [];
        foreach ($this->components as $dim => $val) {
            $newComponents[$dim] = $val + $other->get($dim);
        }
        // Add dimensions from other that are not in this
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
        return new self($newComponents);
    }

    public function multiply(float $scalar): self
    {
        $newComponents = array_map(fn($val) => $val * $scalar, $this->components);
        return new self($newComponents);
    }

    public function magnitude(): float
    {
        $sum = 0;
        foreach ($this->components as $val) {
            $sum += $val * $val;
        }
        return sqrt($sum);
    }

    public function normalize(): self
    {
        $mag = $this->magnitude();
        if ($mag == 0) {
            return $this;
        }
        return $this->multiply(1 / $mag);
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
}
