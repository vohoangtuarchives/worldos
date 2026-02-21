<?php

declare(strict_types=1);

namespace Tuzy\Application\World\GetWorld;

final readonly class GetWorldQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
