<?php

declare(strict_types=1);

namespace Tuzy\Application\Saga\CreateSaga;

final class CreateSagaResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
