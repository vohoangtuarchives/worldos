<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\CreateUniverse;

final class CreateUniverseCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $worldId,
        public readonly string $sagaId,
    ) {
    }
}
