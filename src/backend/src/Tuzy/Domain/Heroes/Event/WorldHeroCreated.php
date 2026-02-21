<?php

declare(strict_types=1);

namespace Tuzy\Domain\Heroes\Event;

final class WorldHeroCreated
{
    public function __construct(
        public readonly string $heroId,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
