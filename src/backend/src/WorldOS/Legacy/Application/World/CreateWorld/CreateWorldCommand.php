<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\CreateWorld;

final class CreateWorldCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $preset = 'default',
        public readonly string $originType = 'user_created',
    ) {
    }
}
