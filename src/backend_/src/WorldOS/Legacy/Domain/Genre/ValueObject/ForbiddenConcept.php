<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Genre\ValueObject;

final readonly class ForbiddenConcept
{
    public function __construct(
        public string $reason,
    ) {
    }
}
