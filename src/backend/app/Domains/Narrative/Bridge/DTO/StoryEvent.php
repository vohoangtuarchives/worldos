<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Bridge\DTO;

/**
 * Structured event extracted from chapter content for world mutation.
 */
final readonly class StoryEvent
{
    public function __construct(
        public string $type,
        public float $severity,
        public ?string $location = null,
        public ?string $symbol = null,
        public ?string $actor = null,
    ) {
        $this->severity = max(0.0, min(1.0, $severity));
    }
}
