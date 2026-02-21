<?php

declare(strict_types=1);

namespace Tuzy\Application\World\CreateWorld;

final class CreateWorldCommand
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
