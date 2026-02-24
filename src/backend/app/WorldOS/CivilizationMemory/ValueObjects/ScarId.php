<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class ScarId
{
    public function __construct(
        public string $value,
    ) {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid ScarId: {$value}");
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

    public function __toString(): string
    {
        return $this->value;
    }
}
