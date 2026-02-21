<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\GetEvolutionProfile;

final readonly class GetEvolutionProfileQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
