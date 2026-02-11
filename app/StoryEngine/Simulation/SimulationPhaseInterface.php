<?php

namespace App\StoryEngine\Simulation;

interface SimulationPhaseInterface
{
    public function execute(SimulationContext $context): void;
}
