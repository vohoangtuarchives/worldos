<?php

declare(strict_types=1);

namespace Tuzy\Application\Runtime\CreateUniverse;

final class CreateUniverseResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
