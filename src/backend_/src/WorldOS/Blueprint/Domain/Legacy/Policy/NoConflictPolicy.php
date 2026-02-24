<?php

namespace WorldOS\Blueprint\Domain\Legacy\Policy;

use WorldOS\Blueprint\Domain\Legacy\Contracts\Policy\ConflictPolicy;

class NoConflictPolicy implements ConflictPolicy
{
    public function shouldConflict(array $snapshot, string $aggressorId, string $defenderId): bool
    {
        return false;
    }
}
