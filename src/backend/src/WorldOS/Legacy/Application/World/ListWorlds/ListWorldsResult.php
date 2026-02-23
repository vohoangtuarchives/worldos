<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\ListWorlds;

final readonly class ListWorldsResult
{
    /** @param list<array{id: string, name: string, status: string, health_status: string, current_tick: int, origin_type: string, preset: string}> $worlds */
    public function __construct(
        public array $worlds,
    ) {
    }
}
