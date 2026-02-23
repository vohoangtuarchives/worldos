<?php

namespace App\Policies;

use WorldOS\Blueprint\Domain\Legacy\ValueObject\WorldHealthStatus;
use App\Models\UniverseModel;
use App\Models\World;

/**
 * Universe (runtime) cannot tick when its World is frozen (HALTED).
 */
class UniverseRuntimePolicy
{
    /**
     * Authorize: Universe can be ticked only if it has no world, or its World is not frozen.
     */
    public function tickUniverse(?UniverseModel $universe, ?World $world): bool
    {
        if (!$world) {
            return true;
        }
        return $world->health_status !== WorldHealthStatus::HALTED;
    }
}
