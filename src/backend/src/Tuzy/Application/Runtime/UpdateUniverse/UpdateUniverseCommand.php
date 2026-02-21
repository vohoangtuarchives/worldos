<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\UpdateUniverse;

final readonly class UpdateUniverseCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
