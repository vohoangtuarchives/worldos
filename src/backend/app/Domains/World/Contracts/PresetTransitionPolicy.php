<?php

namespace App\Domains\World\Contracts;

use App\Models\World\WorldState;
use WorldOS\Blueprint\Domain\Legacy\ValueObject\TransitionResult;

interface PresetTransitionPolicy
{
    /**
     * Validate if a transition from the current state to the next preset is allowed.
     */
    public function validate(WorldState $state, WorldPreset $nextPreset): TransitionResult;
}
