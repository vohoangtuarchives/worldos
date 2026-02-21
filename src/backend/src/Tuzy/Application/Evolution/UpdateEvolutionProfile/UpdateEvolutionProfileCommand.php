<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\UpdateEvolutionProfile;

final readonly class UpdateEvolutionProfileCommand
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }
}
