<?php

declare(strict_types=1);

namespace Tuzy\Domain\Evolution\Service;

use Tuzy\Domain\Evolution\ValueObject\CosmicState;
use Tuzy\Domain\Evolution\Contract\AttractorRepositoryInterface;
use Tuzy\Domain\Evolution\ValueObject\WorldSnapshot;
use Tuzy\Domain\Cosmology\Repository\CosmologyRepository;

/**
 * CosmicEvolutionService
 *
 * Orchestrates the deterministic evolution of cosmic state over time.
 * This is the main entry point for "stepping" the universe forward.
 *
 * Pipeline: Receive external energy -> Evolve state -> Check bifurcation -> Return new state.
 */
class CosmicEvolutionService
{
    public function __construct(
        private WaveInterferenceEngine $waveEngine,
        private AttractorRepositoryInterface $attractorRepository
    ) {}

    /**
     * Evolve the cosmic state by one step (default: 100 years).
     *
     * @param CosmicState $currentState The current cosmic state
     * @param float $civilizationResonance Feedback from CivE layer (0.0 to 1.0)
     * @param int $deltaYears Time step size
     * @param float $entropyFeedback Entropy leak from civilization
     * @return CosmicState The next state
     */
    public function step(\Tuzy\Domain\Cosmology\ValueObject\PhysicalLaws $laws, CosmicState $currentState, float $civilizationResonance = 0.0, int $deltaYears = 100, float $entropyFeedback = 0.0): CosmicState
    {
        $nextYear = $currentState->year + $deltaYears;

        // 1. Get external energy from wave engine
        $externalEnergy = $this->waveEngine->energyAt($nextYear);

        // 2. Evolve the state (pure transition)
        $nextState = $currentState->evolve($laws, $externalEnergy, $civilizationResonance, $deltaYears, $entropyFeedback);

        // 3. Check and apply bifurcation
        $nextState = $this->checkBifurcation($nextState, $civilizationResonance);

        return $nextState;
    }

    /**
     * Run simulation for N steps from a starting state.
     *
     * @param CosmicState $initialState Starting point
     * @param int $steps Number of steps to simulate
     * @param int $deltaYears Years per step
     * @return array<CosmicState> Array of states (includes initial)
     */
    public function simulate(CosmicState $initialState, int $steps, int $deltaYears = 100): array
    {
        $trajectory = [$initialState];
        $current = $initialState;

        for ($i = 0; $i < $steps; $i++) {
            $current = $this->step($current, 0.0, $deltaYears);
            $trajectory[] = $current;
        }

        return $trajectory;
    }

    /**
     * Check if the current state should trigger a bifurcation (attractor change).
     *
     * Bifurcation requires:
     * 1. Strain exceeds the current attractor's threshold
     * 2. The combined force (cosmic + civilization resonance) is sufficient
     */
    private function checkBifurcation(CosmicState $state, float $civilizationResonance): CosmicState
    {
        $currentAttractor = $this->attractorRepository->findByCode($state->currentAttractor);

        if (!$currentAttractor) {
            return $state; // Unknown attractor, no transition
        }

        // Total bifurcation force = strain + resonance contribution
        $totalForce = $state->strain + ($civilizationResonance * 0.3);

        if ($totalForce < $currentAttractor->getBifurcationThreshold()) {
            return $state; // Not enough force to leave current basin
        }

        // Determine the target attractor based on state characteristics
        $targetCode = $this->selectTargetAttractor($state, $currentAttractor);

        if ($targetCode === $state->currentAttractor) {
            return $state; // No valid transition
        }

        // Create new state with shifted attractor
        return new CosmicState(
            order: $state->order,
            entropy: $state->entropy,
            energy: $state->energy,
            causality: $state->causality * 0.7, // Partial release on bifurcation
            strain: $state->strain * 0.3,        // Major strain release
            stability: $state->stability,
            currentAttractor: $targetCode,
            year: $state->year,
        );
    }

    /**
     * Select the target attractor based on the current state vector.
     * Deterministic selection — no randomness.
     */
    private function selectTargetAttractor(CosmicState $state, \Tuzy\Domain\Evolution\Entity\AttractorAggregate $currentAttractor): string
    {
        // Actually, V3 code expected generic transitionsTo, but our AttractorAggregate doesn't have it natively.
        // Let's fallback to catalog all attractors if candidates are empty or missing.
        // Let's simply lookup the value object catalog directly for Transitions if needed, or scan repository.
        
        $bestScore = PHP_FLOAT_MAX;
        $bestCode = $state->currentAttractor;

        // Lấy tất cả Attractor qua Catalog để tính target
        $catalog = \Tuzy\Domain\Evolution\ValueObject\Attractor::catalog();
        
        foreach ($catalog as $candidateCode => $candidate) {
            if ($candidateCode === $currentAttractor->getCode()) continue;
            
            // Distance between current state and candidate equilibrium
            $centroid = $candidate->getCentroid();
            $equilibriumEntropy = $centroid[\Tuzy\Domain\Evolution\ValueObject\WorldStateVector::DIMENSION_ENTROPY] ?? 0.5;
            $equilibriumEnergy = $centroid[\Tuzy\Domain\Evolution\ValueObject\WorldStateVector::DIMENSION_ORDER] ?? 0.5; // using Order as proxy for energy here in V3

            $score = abs($state->entropy - $equilibriumEntropy)
                   + abs($state->energy - $equilibriumEnergy);

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestCode = $candidateCode;
            }
        }

        return $bestCode;
    }
}



