<?php

declare(strict_types=1);

namespace Tuzy\Application\World\UpdateWorld;

final readonly class UpdateWorldCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
