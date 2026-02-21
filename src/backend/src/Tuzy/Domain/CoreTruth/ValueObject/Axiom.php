<?php

declare(strict_types=1);

namespace Tuzy\Domain\CoreTruth\ValueObject;

use InvalidArgumentException;

/**
 * Immutable fundamental truth of a universe.
 */
readonly class Axiom
{
    public function __construct(
        public string $id,
        public string $description,
        public bool $isAbsolute = true,
    ) {
        if ($id === '' || $description === '') {
            throw new InvalidArgumentException('Axiom requires valid id and description.');
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
            $data['is_absolute'] ?? true,
        );
    }
}
