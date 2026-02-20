<?php

namespace App\Domains\CoreTruth\ValueObjects;

use InvalidArgumentException;

/**
 * Axiom represents an immutable fundamental truth of a universe.
 */
class Axiom
{
    public function __construct(
        public readonly string $id,
        public readonly string $description,
        public readonly bool $isAbsolute = true
    ) {
        if (empty($id) || empty($description)) {
            throw new InvalidArgumentException("Axiom requires valid id and description.");
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'is_absolute' => $this->isAbsolute,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['description'],
            $data['is_absolute'] ?? true
        );
    }
}
