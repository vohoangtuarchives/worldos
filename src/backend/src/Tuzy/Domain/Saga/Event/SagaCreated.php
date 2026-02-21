<?php

declare(strict_types=1);

namespace Tuzy\Domain\Saga\Event;

final class SagaCreated
{
    public function __construct(
        public readonly string $sagaId,
        public readonly string $sagaName,
    ) {
    }
}
