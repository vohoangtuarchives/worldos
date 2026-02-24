<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\CreateWorld;

final class CreateWorldResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
