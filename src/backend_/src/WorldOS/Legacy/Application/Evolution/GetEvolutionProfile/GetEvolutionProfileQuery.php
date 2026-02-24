<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\GetEvolutionProfile;

final readonly class GetEvolutionProfileQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
