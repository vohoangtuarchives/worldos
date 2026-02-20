<?php

declare(strict_types=1);

namespace Tuzy\Domain\Vietnamese\Event;

final class WorldHeroCreated
{
    public function __construct(
        public readonly string $heroId,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
