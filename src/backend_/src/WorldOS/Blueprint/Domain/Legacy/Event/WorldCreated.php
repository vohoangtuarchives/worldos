<?php

declare(strict_types=1);

namespace WorldOS\Blueprint\Domain\Legacy\Event;

final class WorldCreated
{
    public function __construct(
        public readonly string $worldId,
        public readonly string $worldName,
    ) {
    }
}
