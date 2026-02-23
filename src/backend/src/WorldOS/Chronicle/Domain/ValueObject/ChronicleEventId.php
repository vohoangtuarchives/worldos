<?php

declare(strict_types=1);

namespace WorldOS\Chronicle\Domain\ValueObject;

use InvalidArgumentException;

final class ChronicleEventId
{
    private function __construct(private readonly string $value) {}

    public static function generate(): self
    {
        return new self(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ));
    }

    public static function fromString(string $id): self
    {
        if (empty(trim($id))) {
            throw new InvalidArgumentException('ChronicleEventId cannot be empty.');
        }
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
}
