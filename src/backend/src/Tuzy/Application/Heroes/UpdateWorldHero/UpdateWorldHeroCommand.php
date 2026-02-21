<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\UpdateWorldHero;

final readonly class UpdateWorldHeroCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
