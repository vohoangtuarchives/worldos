<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\CreateUniverse;

final class CreateUniverseCommand
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
