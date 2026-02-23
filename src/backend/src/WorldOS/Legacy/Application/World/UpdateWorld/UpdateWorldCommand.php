<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\UpdateWorld;

final readonly class UpdateWorldCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status,
        public string $healthStatus,
        public int $currentTick,
        public string $originType,
        public string $preset,
    ) {
    }
}
