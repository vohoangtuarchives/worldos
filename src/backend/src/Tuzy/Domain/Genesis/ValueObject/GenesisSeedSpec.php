<?php

declare(strict_types=1);

namespace Tuzy\Domain\Genesis\ValueObject;

/**
 * Immutable value object: specification for a genesis seed (hash + optional metaphysics vector).
 * Domain-only; no Laravel/Eloquent.
 */
final readonly class GenesisSeedSpec
{
    public function __construct(
        public string $seedHash,
        public array $metaphysicsVector = [],
        public float $instability = 0.0,
    ) {
    }

    public static function fromHash(string $seedHash): self
    {
        return new self($seedHash, [], 0.0);
    }
}
