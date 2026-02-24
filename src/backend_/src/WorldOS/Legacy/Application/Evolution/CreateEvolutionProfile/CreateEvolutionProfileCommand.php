<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\CreateEvolutionProfile;

final class CreateEvolutionProfileCommand
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
