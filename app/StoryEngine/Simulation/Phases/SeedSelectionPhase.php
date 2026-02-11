<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\SeedPicker;
use App\StoryEngine\Seed;
use App\StoryEngine\SeedTransition;

class SeedSelectionPhase implements SimulationPhaseInterface
{
    public function execute(SimulationContext $context): void
    {
        $seed = SeedPicker::pick($context->seeds);

        if (!$seed) {
            // Emergency injection if no valid seed
            $seed = new Seed(SeedTransition::TYPE_SOCIAL_PRESSURE, 'personal', 1);
            $context->addSeed($seed);
        }

        $context->activeSeed = $seed;
    }
}
