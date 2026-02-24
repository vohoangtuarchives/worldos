<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\Levers\DeceptionResolver;
use App\StoryEngine\Levers\PerceptionFilter;
use App\StoryEngine\DecisionBias;
use App\StoryEngine\Events\FactionActionEvent;
use App\StoryEngine\Persistence\EventStore;
use WorldOS\World\Application\Services\WorldLawValidator;

class FactionActionPhase implements SimulationPhaseInterface
{
    public function __construct(
        protected WorldLawValidator $validator,
        protected ?EventStore $eventStore = null
    ) {}

    public function execute(SimulationContext $context): void
    {
        // 1. Misinformation (Phase W) - SKIPPED IN SAFE MODE
        if (!$context->safeMode && rand(0, 100) < 10) { 
            $liar = $context->world->factions[array_rand($context->world->factions)];
            $fakeSeed = DeceptionResolver::spread($liar);
            
            $validatedFake = $this->validator->validateSeedApplication($context->world->lawProfile, $fakeSeed);
            if ($validatedFake) {
                $context->addSeed($validatedFake);
            }
        }

        // 2. Faction Interaction
        $actingFaction = $context->world->factions[array_rand($context->world->factions)];
        
        // 3. Perception Filter (Phase W)
        $visibleSeeds = PerceptionFilter::apply($context->seeds, $actingFaction);
        
        if (!empty($visibleSeeds)) {
            $targetSeed = $visibleSeeds[array_rand($visibleSeeds)];
            $intent = $targetSeed->type;
            
            // Adjust Risk
            $baseRisk = 0.5; 
            $adjustedRisk = DecisionBias::adjustRisk($actingFaction, $baseRisk);
            
            $outcome = 'failure';
            
            // Handle Deception
            if ($targetSeed instanceof \App\StoryEngine\InformationSeed) {
                if ($targetSeed->truthfulness < 0.5) {
                    $outcome = 'disaster'; // It was a trap/lie
                    $context->removeSeed($targetSeed);
                } else {
                    $outcome = 'success';
                    $context->removeSeed($targetSeed);
                }
            } else {
                // Normal Logic
                $roll = rand(0, 100);
                $difficulty = $targetSeed->severity * 10;
                $outcome = ($roll + $actingFaction->cohesion) > $difficulty ? 'success' : 'failure';
                if ($difficulty > 80 && $outcome === 'failure') $outcome = 'disaster';
            }
            
            // Emit Event
            $event = new FactionActionEvent(
                $context->currentChapter,
                $context->timelineId,
                $actingFaction->id,
                $intent,
                $outcome
            );
            
            // Apply Change
            $event->apply($context->world);
            
            // Persist
            if ($this->eventStore) {
                $this->eventStore->append($event);
            }
        }
    }
}
