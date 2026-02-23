<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Cosmology\Event;

final class UniverseStyleCreated
{
    public function __construct(
        public readonly string $universeStyleId,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
