<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\CreateWorldHero;

final class CreateWorldHeroResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $worldId,
    ) {
    }
}
