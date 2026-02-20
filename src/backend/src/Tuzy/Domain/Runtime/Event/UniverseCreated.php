<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\Event;

final class UniverseCreated
{
    public function __construct(
        public readonly string $universeId,
        public readonly string $universeName,
    ) {
    }
}
