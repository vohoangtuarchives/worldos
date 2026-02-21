<?php

declare(strict_types=1);

namespace Tuzy\Domain\Narrative\ValueObject;

/**
 * Compressed memory for continuity: digest + last paragraphs instead of raw story_so_far.
 */
readonly class MemorySnapshot
{
    /** @param list<string> $unresolvedConflicts */
    public function __construct(
        public string $lastBeat,
        public float $arcProgress,
        public array $unresolvedConflicts,
        public string $digest,
        public string $lastParagraphs,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->digest === '' && $this->lastParagraphs === '';
    }
}
