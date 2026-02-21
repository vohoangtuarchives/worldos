<?php

namespace Tuzy\Domain\Reader;

use Tuzy\Application\Material\State\WorldStateRepository;
use Tuzy\Application\Material\State\WorldStateMutator;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * ReaderInteractionService - Orchestrates reader interactions.
 */
class ReaderInteractionService
{
    public function __construct(
        private ChoicePresenter $presenter,
        private InfluencePropagator $propagator,
        private EthicalGuard $guard,
        private WorldStateRepository $stateRepo,
        private WorldStateMutator $mutator
    ) {}

    /**
     * Get available choices for the current state.
     */
    public function getChoices(string $worldId): array
    {
        $state = $this->stateRepo->getCurrentState($worldId);
        // Assuming we store current epoch in world table or calculate it
        // For now, getting it from state or passing it is tricky if not stored.
        // Let's assume passed or obtained from a World service. 
        // We'll use a placeholder epoch 0 if not available, since choices rely mainly on state values.
        $epoch = $this->getCurrentEpoch($worldId);
        
        return $this->presenter->generateChoices($state, $epoch);
    }

    /**
     * Apply a reader's choice to the world.
     */
    public function applyChoice(string $worldId, string $choiceId, string $optionId, array $choices): void
    {
        // 1. Find the selected option
        $selectedOption = null;
        foreach ($choices as $choice) {
            if ($choice['id'] === $choiceId) {
                foreach ($choice['options'] as $option) {
                    if ($option['id'] === $optionId) {
                        $selectedOption = $option;
                        break 2;
                    }
                }
            }
        }

        if (!$selectedOption) {
            throw new Exception("Invalid choice or option ID.");
        }

        // 2. Validate Ethical Constraints
        // We wrap the option in a structure expected by EthicalGuard (usually full choice, but here we validate the selected option's delta)
        $simulatedChoice = ['options' => [$selectedOption]];
        $validation = $this->guard->validateChoice($simulatedChoice);
        if (!$validation['valid']) {
            throw new Exception("Ethical Guard Constraint: " . implode(", ", $validation['errors']));
        }

        // 3. Calculate Influence (Dampening)
        $currentState = $this->stateRepo->getCurrentState($worldId);
        // We artificially structure this as a single 'vote' for the propagator
        $vote = ['delta' => $selectedOption['delta']];
        $deltas = $this->propagator->propagate([$vote], $currentState);

        // 4. Apply to World State (Immediate Intervention)
        $newState = $this->mutator->applyDeltas($currentState, $deltas, ['READER_INTERVENTION' => ['Human']]);
        
        // 5. Persist the change (as an 'Intervention Event')
        // We use a special epoch marker or just append to history?
        // Better: Update the 'current' state in memory/cache/db so next tick uses it.
        // Since we don't have a specific 'updateCurrentState' method exposed yet, we might need one.
        // But saveSnapshot updates the latest snapshot.
        // Actually, WorldStateRepository stores events. To make this stick, we should save a 'Manual Event'.
        
        $epoch = $this->getCurrentEpoch($worldId);
        $this->stateRepo->saveEvent(
            $worldId,
            $epoch, 
            $deltas, 
            ['READER_INTERVENTION' => 'User Choice'], 
            ['manual_intervention' => true]
        );
        
        // Also save snapshot to ensure next tick loads this state
        $this->stateRepo->saveSnapshot($newState);
    }

    private function getCurrentEpoch(string $worldId): int
    {
        // Simple query to get max epoch
        return DB::table('world_state_events')
            ->where('world_id', $worldId)
            ->max('epoch') ?? 0;
    }
}
