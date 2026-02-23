<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Heroes\ListHeroes;

final readonly class ListHeroesResult
{
    /** @param list<array{id: string, name: string, world_id: string}> $Heroes */
    public function __construct(
        public array $Heroes,
    ) {
    }
}
