<?php

declare(strict_types=1);

namespace Tuzy\Domain\World\Event;

final class WorldCreated
{
    public function __construct(
        public readonly string $worldId,
        public readonly string $worldName,
    ) {
    }
}
