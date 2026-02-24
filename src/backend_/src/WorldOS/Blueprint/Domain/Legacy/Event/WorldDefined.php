<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Event;

/**
 * World (aggregate root) was created. RuntimeContext may react (e.g. create initial Universe instance).
 */
readonly class WorldDefined
{
    public function __construct(
        public string $worldId,
        public string $name,
        public array $baselineLaws = [],
    ) {
    }
}
