<?php

declare(strict_types=1);

namespace Tuzy\Application\Heroes\ListWorldHeroes;

final readonly class ListWorldHeroesResult
{
    /** @param list<array{id: string, name: string, world_id: string}> $worldHeroes */
    public function __construct(
        public array $worldHeroes,
    ) {
    }
}
