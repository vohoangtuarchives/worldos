<?php

namespace App\StoryEngine\Simulation\Phases;

use App\StoryEngine\Simulation\SimulationPhaseInterface;
use App\StoryEngine\Simulation\SimulationContext;
use App\StoryEngine\SeedTransition;
use App\StoryEngine\RuleApplier;
use WorldOS\World\Application\Services\WorldLawValidator;

class UnifiedRulePhase implements SimulationPhaseInterface
{
    public function __construct(
        protected WorldLawValidator $validator
    ) {}

    public function execute(SimulationContext $context): void
    {
        $seed = $context->activeSeed;
        if (!$seed) return;

        // Resolve & Transition
        $context->removeSeed($seed); // Remove active seed before resolving
        $newSeeds = SeedTransition::resolve($seed);

        // Apply Rules
        RuleApplier::apply(
            $context->world, 
            $context->character, 
            $context->seeds, 
            $newSeeds, 
            $seed, 
            $this->validator
        );
        
        // RuleApplier typically modifies $seeds inside $world context via reference in original code,
        // but here we pass $context->seeds.
        // RuleApplier::apply signature: (WorldState $world, CharacterState $char, array &$seeds, array $newSeeds, Seed $causedBy, ...)
        
        // IMPORTANT: Because $context->seeds is an array properly, we need to ensure RuleApplier updates it.
        // In this implementation, RuleApplier takes &$seeds by reference. 
        // We need to pass $context->seeds to it, but verify if PHP updates the object property array.
        // Standard PHP behavior: passing $context->seeds (which is an array property) to a function expecting reference &$seeds works.
    }
}
