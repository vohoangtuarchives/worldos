<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

/**
 * Immutable segment of narrative: paragraphs and next cursor.
 */
readonly class StorySlice
{
    public function __construct(
        public array $paragraphs = [],
        public int $nextCursor = 0,
    ) {
    }

    public function toArray(): array
    {
        return [
            'paragraphs' => $this->paragraphs,
            'next_cursor' => $this->nextCursor,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['paragraphs'] ?? [],
            (int) ($data['next_cursor'] ?? 0),
        );
    }
}
