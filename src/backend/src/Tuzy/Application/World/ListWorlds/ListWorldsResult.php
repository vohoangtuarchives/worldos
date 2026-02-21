<?php

declare(strict_types=1);

namespace Tuzy\Application\World\ListWorlds;

final readonly class ListWorldsResult
{
    /** @param list<array{id: string, name: string}> $worlds */
    public function __construct(
        public array $worlds,
    ) {
    }
}
