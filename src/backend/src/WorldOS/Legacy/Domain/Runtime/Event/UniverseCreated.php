<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Runtime\Event;

final class UniverseCreated
{
    public function __construct(
        public readonly string $universeId,
        public readonly string $universeName,
    ) {
    }
}
