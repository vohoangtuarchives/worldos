<?php

namespace App\Domains\World\Policy;

use App\Domains\World\Contracts\Policy\ConflictPolicy;

class NoConflictPolicy implements ConflictPolicy
{
    public function shouldConflict(array $snapshot, string $aggressorId, string $defenderId): bool
    {
        return false;
    }
}
