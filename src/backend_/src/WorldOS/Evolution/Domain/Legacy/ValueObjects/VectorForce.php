<?php

declare(strict_types=1);

namespace WorldOS\Evolution\Domain\Legacy\ValueObjects;

use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * VectorForce - Immutable delta per dimension (force vector in phase space).
 * Same dimensions as WorldStateVector; each component is a delta (can be positive or negative).
 */
final class VectorForce
{
    public function __construct(
        private readonly array $components
    ) {
    }

    public static function zero(): self
    {
        $dims = [];
        foreach (WorldStateVector::dimensions() as $dim) {
            $dims[$dim] = 0.0;
        }
        return new self($dims);
    }

    public function get(string $dimension): float
    {
        return $this->components[$dimension] ?? 0.0;
    }

    public function getAll(): array
    {
        return $this->components;
    }

    public function add(VectorForce $other): self
    {
        $dims = WorldStateVector::dimensions();
        $new = [];
        foreach ($dims as $dim) {
            $new[$dim] = $this->get($dim) + $other->get($dim);
        }
        return new self($new);
    }

    public function multiply(float $scalar): self
    {
        $new = array_map(fn (float $v): float => $v * $scalar, $this->components);
        return new self($new);
    }

    public function magnitude(): float
    {
        $sum = 0.0;
        foreach ($this->components as $v) {
            $sum += $v * $v;
        }
        return sqrt($sum);
    }
}
