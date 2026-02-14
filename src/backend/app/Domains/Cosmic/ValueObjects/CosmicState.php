<?php

declare(strict_types=1);

namespace App\Domains\Cosmic\ValueObjects;

use InvalidArgumentException;

/**
 * CosmicState - Immutable Value Object representing the cosmic field at a moment in time.
 *
 * This is the "state vector" of the universe.
 * All transitions are deterministic: S(t+1) = F(S(t), energy(t)).
 */
final class CosmicState
{
    public function __construct(
        public readonly float $entropy,         // Order disorder (0.0 = perfect order, 1.0 = maximum chaos)
        public readonly float $energy,          // Energy density from wave engine (externally computed)
        public readonly float $causality,       // Causality tension (accumulated causal pressure)
        public readonly float $strain,          // Structural strain on the cosmic fabric
        public readonly float $stability,       // Overall stability gradient (derived)
        public readonly string $currentAttractor, // Identifier of the current regime
        public readonly int $year,              // The cosmic year this state represents
        public readonly ?string $currentIncarnationId = null,
        public readonly ?array $morphTargetCentroid = null,
        public readonly ?int $morphStartTick = null,
        public readonly float $morphIntensity = 1.0
    ) {
        $this->validate();
    }

    /**
     * Create the default initial observation state.
     * This is NOT "year 0" — it's just the default entry point for observation.
     */
    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            entropy: 0.20,
            energy: 0.60,
            causality: 0.30,
            strain: 0.05,
            stability: 0.80,
            currentAttractor: 'EQUILIBRIUM',
            year: $year,
        );
    }

    /**
     * CORE TRANSITION FUNCTION: S(t+1) = F(S(t), externalEnergy)
     *
     * @param float $externalEnergy Energy density from WaveInterferenceEngine
     * @param float $civilizationResonance Feedback from civilization layer (0.0 to 1.0)
     * @param int $deltaYears Time step in years
     * @return self The next cosmic state
     */
    public function evolve(float $externalEnergy, float $civilizationResonance = 0.0, int $deltaYears = 100): self
    {
        // Initial values
        $ent = $this->entropy;
        $en = $externalEnergy; // Energy is driven externally, but we might smooth it? No, take as is.
        $cau = $this->causality;
        $str = $this->strain;
        $stab = $this->stability;

        // Constants (tuned for 1-year step)
        $alpha = 0.0004; // Entropy natural growth
        $beta = 0.0003;  // Entropy reduction by energy
        $strainFeedbackFactor = 0.002; // Strength of dStrain ~ Entropy * Strain

        // Internal Loop for Stability (dt = 1 year)
        for ($i = 0; $i < $deltaYears; $i++) {
            
            // 1. Entropy
            // dH/dt = alpha * H * (1-H) - beta * Energy
            $entChange = ($alpha * $ent * (1.0 - $ent)) - ($beta * $en);
            $ent = max(0.0, min(1.0, $ent + $entChange));

            // 2. Causality
            // Grows with entropy and energy
            $cauChange = ($ent * 0.0003) + ($en * 0.0002) - 0.0001;
            $cau = max(0.0, min(2.0, $cau + $cauChange));

            // 3. Stability
            // Inverse of entropy
            $stab = 1.0 - $ent;

            // 4. Strain (The Positive Feedback Loop)
            // dStrain/dt = k * Entropy * Strain (Exponential runaway if H > 0)
            // + Civilization Resonance forcing
            
            $strainRunaway = $strainFeedbackFactor * $ent * $str;
            $strainResonance = $civilizationResonance * 0.0005;
            $strainRecovery = $stab * 0.0002; // Stability heals strain

            $strChange = $strainRunaway + $strainResonance - $strainRecovery;
            
            // Ensure Strain doesn't get stuck at 0 (need a tiny spark if H is high)
            if ($str < 0.01 && $ent > 0.5) {
                $strChange += 0.0001; 
            }

            $str = max(0.0, min(2.0, $str + $strChange));
        }

        // Check for Fracture (Collapse Condition)
        // NOT resetting here. Just clamping or marking. 
        // BifurcationManager will handle the actual regime shift.
        $newAttractor = $this->currentAttractor;
        
        // Return new state
        return new self(
            entropy: round($ent, 6),
            energy: round($en, 6),
            causality: round($cau, 6),
            strain: round($str, 6),
            stability: round($stab, 6),
            currentAttractor: $newAttractor,
            year: $this->year + $deltaYears,
        );
    }

    /**
     * Check if the state meets the structural collapse criteria.
     * Used by BifurcationManager.
     */
    public function isCritical(float $resilience): bool
    {
        // Collapse if Strain is high AND Civilization Resilience is low
        return ($this->strain > 0.9 && $resilience < 0.2);
    }

    /**
     * Calculate the "cosmic tension" — a composite metric for narrative rendering.
     */
    public function cosmicTension(): float
    {
        return ($this->entropy * 0.3 + $this->causality * 0.3 + $this->strain * 0.4);
    }

    private function validate(): void
    {
        if ($this->entropy < 0.0 || $this->entropy > 1.0) {
            throw new InvalidArgumentException("Entropy must be between 0.0 and 1.0, got {$this->entropy}");
        }
        if ($this->energy < 0.0 || $this->energy > 1.0) {
            throw new InvalidArgumentException("Energy must be between 0.0 and 1.0, got {$this->energy}");
        }
    }
}
