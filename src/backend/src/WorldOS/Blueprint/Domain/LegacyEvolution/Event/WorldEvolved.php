<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\LegacyEvolution\Event;

/**
 * Domain event: world state evolved (cascade / tick).
 * Domain-only; no Eloquent.
 */
final readonly class WorldEvolved
{
    public function __construct(
        public string $worldId,
        public string $epochId,
        public array $delta = [],
    ) {
    }
}
