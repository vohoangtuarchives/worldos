<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Runtime\GetUniverse;

final readonly class GetUniverseQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
