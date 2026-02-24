<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\World\ValueObject;

final class WorldId
{
    public function __construct(private readonly string $value)
    {
        if (empty(trim($this->value))) {
            throw new \InvalidArgumentException('WorldId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);
        return new self(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4)));
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
