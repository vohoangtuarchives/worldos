<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Event;

/**
 * World laws were updated. RuntimeContext (Universe instances) may react to apply new rules.
 */
readonly class WorldLawUpdated
{
    public function __construct(
        public string $worldId,
        public array $previousLaws,
        public array $newLaws,
    ) {
    }
}
