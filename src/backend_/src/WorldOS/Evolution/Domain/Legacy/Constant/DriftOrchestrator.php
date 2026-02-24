<?php

namespace WorldOS\Evolution\Domain\Legacy\Constant;

use WorldOS\Evolution\Domain\Legacy\Constant\Policies\DriftPolicyInterface;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldSeed;
use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector;
use Exception;

class DriftOrchestrator
{
    private array $policies = [];

    public function registerPolicy(string $archetypeId, DriftPolicyInterface $policy): void
    {
        $this->policies[$archetypeId] = $policy;
    }

    /**
     * Compute cumulative drift and output the next state.
     */
    public function computeNextState(
        WorldSeed $seed,
        WorldStateVector $currentState
    ): WorldStateVector {
        $policy = $this->policies[$seed->archetypeId] ?? null;

        if (!$policy) {
            // Apply default uniform drift logic if no policy
            return $this->applyDefaultStochasticDrift($seed, $currentState);
        }

        $drifts = $policy->calculateDrift($seed, $currentState);

        // Merge drifts to state vectors
        // Logic currently returns the original state modified by drift components
        return $this->applyCalculatedDrifts($currentState, $drifts);
    }

    private function applyDefaultStochasticDrift(
        WorldSeed $seed,
        WorldStateVector $state
    ): WorldStateVector {
        $volatility = $seed->driftProfile['volatility'] ?? 0.1;
        $baselineRate = $seed->driftProfile['baseline_rate'] ?? 0.01;

        $newValues = [];
        foreach (WorldStateVector::dimensions() as $dim) {
            $current = $state->get($dim);
            $noise = (lcg_value() * 2 - 1) * $volatility;
            $newValues[$dim] = max(0.0, min(1.0, $current + $baselineRate + $noise));
        }

        return WorldStateVector::fromArray($newValues);
    }

    private function applyCalculatedDrifts(WorldStateVector $state, array $drifts): WorldStateVector
    {
        $newValues = [];
        foreach (WorldStateVector::dimensions() as $dim) {
            $val = $state->get($dim) + ($drifts[$dim] ?? 0.0);
            $newValues[$dim] = max(0.0, min(1.0, $val));
        }
        
        return WorldStateVector::fromArray($newValues);
    }
}


