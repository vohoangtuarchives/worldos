<?php

namespace WorldOS\Blueprint\Domain\Legacy\Contracts;

use WorldOS\Legacy\Application\World\Support\DeterministicRandom;
use WorldOS\Blueprint\Domain\Legacy\Contracts\WorldPreset;

interface TickProcessor
{
    /**
     * Process the world snapshot and return a new modified snapshot.
     * 
     * @param array $snapshot The immutable snapshot of the current state
     * @param WorldPreset $preset The preset configuration
     * @param DeterministicRandom $random Deterministic RNG
     * @return array The new snapshot (must be a new array or modified copy)
     */
    public function process(array $snapshot, WorldPreset $preset, DeterministicRandom $random): array;
}
