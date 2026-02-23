<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Balancing\BalancingApplier;
use WorldOS\World\Application\Services\WorldLawValidator;

class BalancingPhase implements SimulationPhaseInterface
{
    public function __construct(
        protected WorldLawValidator $validator
    ) {}

    public function execute(SimulationContext $context): void
    {
        // BalancingApplier expects array reference for seeds
        // We pass the context properties
        
        BalancingApplier::apply($context->world, $context->seeds, $this->validator);
    }
}
