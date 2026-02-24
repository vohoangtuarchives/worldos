<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\GetHero;

final readonly class GetHeroQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
