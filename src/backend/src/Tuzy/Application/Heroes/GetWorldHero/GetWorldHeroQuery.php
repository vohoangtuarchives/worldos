<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\GetWorldHero;

final readonly class GetWorldHeroQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
