<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\CreateUniverseStyle;

final class CreateUniverseStyleResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
