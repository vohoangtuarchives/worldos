<?php

namespace Tuzy\Domain\World\Policy;

use Tuzy\Domain\World\Contracts\Policy\ConflictPolicy;

class NoConflictPolicy implements ConflictPolicy
{
    public function shouldConflict(array $snapshot, string $aggressorId, string $defenderId): bool
    {
        return false;
    }
}
