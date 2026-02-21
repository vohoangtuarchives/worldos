<?php

declare(strict_types=1);

namespace Tuzy\Application\Cosmology\UpdateUniverseStyle;

final readonly class UpdateUniverseStyleCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
