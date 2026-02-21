<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\ListSagas;

final readonly class ListSagasResult
{
    /** @param list<array{id: string, name: string}> $sagas */
    public function __construct(
        public array $sagas,
    ) {
    }
}
