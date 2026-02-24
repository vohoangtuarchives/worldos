<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Cosmology\CreateUniverseStyle;

final class CreateUniverseStyleCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
