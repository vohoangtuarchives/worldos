<?php

namespace WorldOS\Legacy\Application\Evolution\Services;

use WorldOS\Evolution\Domain\Legacy\Models\EvolutionProfile;
use WorldOS\Evolution\Domain\Legacy\ValueObjects\StateVector;

class EvolutionKernel
{
    /**
     * Calculate the state vector for the next tick (t+1).
     *
     * @param StateVector $current The state at time t
     * @param EvolutionProfile $profile The laws of physics for this world
     * @return StateVector The state at time t+1
     */
    public function nextTick(StateVector $current, EvolutionProfile $profile): StateVector
    {
        // 1. Extract coefficients
        $c = $profile->coefficients;
        $alpha = $profile->alpha;

        // 2. Calculate Deltas based on differential equations
        // d(Belief) = c1 * Coherence * Population(implied) - Decay
        $dBelief = ($c['belief_growth'] ?? 0.05) * $current->coherence * $alpha - 0.01;
        
        // d(Entropy) = c2 * ResourceFlow(inverted) + Contradiction
        $dEntropy = ($c['entropy_decay'] ?? 0.02) * (1.0 - $current->resource_flow) + ($current->contradiction_index * 0.1);
        
        // d(Stability) = c3 * Coherence - c4 * Entropy
        $dStability = ($c['stability_recovery'] ?? 0.05) * $current->coherence - ($c['instability_factor'] ?? 0.05) * $current->entropy;

        // d(ResourceFlow) = Innovation - Consumption
        $dResource = ($c['innovation_bonus'] ?? 0.02) * $current->innovation_rate - ($c['resource_consumption'] ?? 0.05);

        // 3. Apply changes (clamping handled in StateVector)
        $next = new StateVector(
            coherence: $this->clamp($current->coherence + 0.0), // Constant for now unless externally perturbed
            entropy: $this->clamp($current->entropy + $dEntropy),
            belief_mass: $this->clamp($current->belief_mass + $dBelief),
            resource_flow: $this->clamp($current->resource_flow + $dResource),
            stability: $this->clamp($current->stability + $dStability),
            innovation_rate: $this->clamp($current->innovation_rate), // Genetic constant?
            contradiction_index: $this->clamp($current->contradiction_index + 0.001), // Slowly rises naturally
            latent_variables: $current->latent_variables
        );

        return $next;
    }

    protected function clamp(float $val): float
    {
        return max(0.0, min(1.0, $val));
    }
}
