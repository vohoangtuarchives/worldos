<?php

namespace App\Domains\Narrative\ValueObjects;

/**
 * StorySlice Value Object
 * Represents a segment of narrative, immutable.
 */
class StorySlice
{
    public function __construct(
        public readonly array $paragraphs,
        public readonly int $nextCursor
    ) {}
}
