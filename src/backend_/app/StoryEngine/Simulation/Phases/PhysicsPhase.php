<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;

class PhysicsPhase implements SimulationPhaseInterface
{
    public function execute(SimulationContext $context): void
    {
        // 0. Age Character Tier
        $context->character->chaptersInCurrentTier++;

        // 1. Age all seeds
        foreach ($context->seeds as $seed) {
            $seed->age++;
        }
    }
}
