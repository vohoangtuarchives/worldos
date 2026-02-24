<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Levers\EconomicPressureResolver;
use WorldOS\World\Application\Services\WorldLawValidator;

class EconomicPhase implements SimulationPhaseInterface
{
    public function __construct(
        protected WorldLawValidator $validator
    ) {}

    public function execute(SimulationContext $context): void
    {
        foreach ($context->world->factions as $f) {
            // Consumption
            $consumption = rand(5, 15);
            $f->economy->consume($consumption);
            
            // Production
            $production = rand(0, 20); 
            $f->economy->produce($production);
            
            // Check Pressure
            $econSeeds = EconomicPressureResolver::apply($f);
            foreach ($econSeeds as $es) {
                $validatedEcon = $this->validator->validateSeedApplication($context->world->lawProfile, $es);
                if ($validatedEcon) {
                    $context->addSeed($validatedEcon);
                }
            }
        }
    }
}
