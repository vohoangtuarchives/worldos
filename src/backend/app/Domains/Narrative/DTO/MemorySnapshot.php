<?php

declare(strict_types=1);

namespace App\Domains\Narrative\DTO;

/**
 * Compressed memory for continuity: digest + last paragraphs instead of raw story_so_far.
 */
final readonly class MemorySnapshot
{
    public function __construct(
        public string $lastBeat,
        public float $arcProgress,
        /** @var list<string> */
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
