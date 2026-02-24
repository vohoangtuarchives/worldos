<?php

declare(strict_types=1);

namespace App\WorldOS\World\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * World Identifier — UUID-based Value Object.
 *
 * Immutable. Used as the identity of a WorldEntity.
 */
final readonly class WorldId
{
    public function __construct(
        public string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('WorldId cannot be empty');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $id): self
    {
        return new self($id);
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
