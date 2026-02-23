<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\CreateHero;

final class CreateHeroCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
