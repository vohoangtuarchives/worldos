<?php

namespace App\Domains\Genre\Validation\Violations;

class PowerLevelViolation
{
    public function __construct(
        public string $reason,
        public string $actorId,
        public string $targetId
    ) {}
}
