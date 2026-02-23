<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\UpdateHero;

final readonly class UpdateHeroCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
