<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\GetSaga;

final readonly class GetSagaQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
