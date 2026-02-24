<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Attractor identifier — UUID-based Value Object.
 */
final readonly class AttractorId
{
    public function __construct(
        public string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid AttractorId: {$value}");
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
