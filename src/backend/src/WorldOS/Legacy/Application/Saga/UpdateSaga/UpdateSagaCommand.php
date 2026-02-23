<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Saga\UpdateSaga;

final readonly class UpdateSagaCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
