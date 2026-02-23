<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Genre\ValueObject;

/**
 * Event payload for genre validation (combat, death, resurrection, etc.).
 */
final readonly class StoryEvent
{
    public function __construct(
        public string $type,
        public array $payload = [],
    ) {
    }
}
