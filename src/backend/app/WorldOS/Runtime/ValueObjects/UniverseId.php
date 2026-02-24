<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Universe Identifier — UUID-based Value Object.
 */
final readonly class UniverseId
{
    public function __construct(
        public string $value,
    ) {
        if (empty($value)) {
            throw new InvalidArgumentException('UniverseId cannot be empty');
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
