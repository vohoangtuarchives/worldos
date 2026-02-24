<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\UpdateUniverse;

final readonly class UpdateUniverseCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public int $age,
        public string $status,
        public float $entropy,
        public float $stabilityIndex,
    ) {
    }
}
