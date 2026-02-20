<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\CreateEvolutionProfile;

final class CreateEvolutionProfileResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {
    }
}
