<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Narrative\ValueObject;

/**
 * Structured event extracted from chapter content for world mutation.
 */
readonly class StoryEvent
{
    public function __construct(
        public string $type,
        public float $severity,
        public ?string $location = null,
        public ?string $symbol = null,
        public ?string $actor = null,
    ) {
    }
}
