<?php

namespace App\Domains\Reader;

use App\Domains\Material\State\WorldState;
use App\Domains\Material\State\WorldStateRepository;

/**
 * ReaderInteractionEngine - Orchestrate reader interaction cycle
 * 
 * Integrates all 6 components for full reader influence flow.
 */
class ReaderInteractionEngine
{
    public function __construct(
        private ChoicePresenter $presenter,
        private VoteAggregator $aggregator,
        private InfluencePropagator $propagator,
        private ReactionProcessor $reactionProcessor,
        private EthicalGuard $guard,
        private InteractionLogger $logger,
        private WorldStateRepository $stateRepository
    ) {}

    /**
     * Execute reader interaction cycle for an epoch.
     * 
     * @param string $worldId
     * @param int $epoch
     * @return array Interaction results
     */
    public function execute(string $worldId, int $epoch): array
    {
        // 1. Get current state
        $state = $this->stateRepository->getCurrentState($worldId);

        // 2. Generate choices
        $choices = $this->presenter->generateChoices($state, $epoch);

        // 3. Validate choices
        foreach ($choices as $choice) {
            $validation = $this->guard->validateChoice($choice);
            if (!$validation['valid']) {
                throw new \Exception('Invalid choice: ' . implode(', ', $validation['errors']));
            }
        }

        // 4. Get votes from database
        $allVotes = $this->logger->getVotesForEpoch($worldId, $epoch);

        // 5. Aggregate votes for each choice
        $aggregatedVotes = [];
        foreach ($choices as $choice) {
            $choiceVotes = array_filter(
                $allVotes,
                fn($v) => $v->choice_id === $choice['id']
            );

            $result = $this->aggregator->aggregateWeighted(
                $choice['id'],
                array_map(fn($v) => (array) $v, $choiceVotes),
                $choice['options']
            );

            $aggregatedVotes[] = $result;

            // Log result
            $this->logger->logResult($worldId, $epoch, $result);
        }

        // 6. Calculate influence deltas
        $deltas = $this->propagator->propagate($aggregatedVotes, $state);

        // 7. Process reactions
        $reactions = $this->logger->getReactionsForEpoch($worldId, $epoch);
        $reactionDeltas = $this->reactionProcessor->process(
            array_map(fn($r) => (array) $r, $reactions),
            $epoch
        );

        // 8. Merge deltas
        $finalDeltas = $this->mergeDeltas($deltas, $reactionDeltas);

        // 9. Validate final deltas
        $deltaValidation = $this->guard->validateDelta($finalDeltas);
        if (!$deltaValidation['valid']) {
            throw new \Exception('Invalid deltas: ' . implode(', ', $deltaValidation['errors']));
        }

        return [
            'choices' => $choices,
            'votes' => $aggregatedVotes,
            'reactions' => count($reactions),
            'deltas' => $finalDeltas,
            'dampening_factor' => $this->propagator->getDampeningFactor(),
        ];
    }

    /**
     * Merge multiple delta arrays.
     */
    private function mergeDeltas(array ...$deltaArrays): array
    {
        $merged = [];

        foreach ($deltaArrays as $deltas) {
            foreach ($deltas as $field => $value) {
                $merged[$field] = ($merged[$field] ?? 0) + $value;
            }
        }

        return $merged;
    }

    /**
     * Get available choices for epoch (without executing).
     */
    public function getChoices(string $worldId, int $epoch): array
    {
        $state = $this->stateRepository->getCurrentState($worldId);
        return $this->presenter->generateChoices($state, $epoch);
    }
}
