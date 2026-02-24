<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\World\GetWorld;

final readonly class GetWorldQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
