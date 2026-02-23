<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Domain\Genre\ValueObject;

final readonly class PowerLevelViolation
{
    public function __construct(
        public string $reason,
        public string $actorId,
        public string $targetId,
    ) {
    }
}
