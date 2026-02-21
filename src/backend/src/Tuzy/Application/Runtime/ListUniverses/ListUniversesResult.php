<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\ListUniverses;

final readonly class ListUniversesResult
{
    /** @param list<array{id: string, name: string}> $universes */
    public function __construct(
        public array $universes,
    ) {
    }
}
