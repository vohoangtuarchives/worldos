<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\ListUniverseStyles;

final readonly class ListUniverseStylesResult
{
    /** @param list<array{id: string, name: string, world_id: string}> $universeStyles */
    public function __construct(
        public array $universeStyles,
    ) {
    }
}
