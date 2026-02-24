<?php

namespace WorldOS\Legacy\Application\World\Interaction\Presets;

use WorldOS\Blueprint\Domain\Legacy\WorldState;

interface PresetInteraction
{
    public function applyInteraction(WorldState $worldA, WorldState $worldB): void;
    public function getInteractionType(): string;
    public function calculateCompatibility(WorldState $worldA, WorldState $worldB): float;
    public function canHybridize(WorldState $worldA, WorldState $worldB): bool;
}
