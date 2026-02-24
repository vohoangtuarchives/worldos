<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\CreateSaga;

final class CreateSagaCommand
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
