<?php

declare(strict_types=1);

namespace App\WorldOS\Shared\ValueObjects;

/**
 * Seed — Deterministic random seed for Universe RNG.
 *
 * Used to ensure reproducibility: same seed → same evolution
 * if no external events intervene.
 *
 * Immutable Value Object.
 */
final readonly class Seed
{
    public function __construct(
        public int $value,
    ) {
    }

    /**
     * Generate a new random seed.
     */
    public static function generate(): self
    {
        return new self(mt_rand());
    }

    /**
     * Derive a child seed from parent + tick for deterministic branching.
     */
    public function deriveForTick(int $tick): self
    {
        return new self($this->value ^ ($tick * 2654435761));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
