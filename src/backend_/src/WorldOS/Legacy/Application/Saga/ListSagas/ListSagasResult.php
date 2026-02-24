<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\ListSagas;

final readonly class ListSagasResult
{
    /** @param list<array{id: string, name: string}> $sagas */
    public function __construct(
        public array $sagas,
    ) {
    }
}
