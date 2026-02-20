<?php

declare(strict_types=1);

namespace WorldOS\Domains\Evolution\ValueObjects;

use WorldOS\Domains\Cosmology\ValueObjects\PhysicalLaws;
use InvalidArgumentException;
use Illuminate\Contracts\Support\Arrayable;

/**
 * CosmicState - Immutable Value Object representing the cosmic field at a moment in time.
 *
 * This is the "state vector" of the universe.
 * All transitions are deterministic: S(t+1) = F(S(t), energy(t)).
 */
final class CosmicState
{
    public function __construct(
        public readonly float $order,           // Structural coherence (0.0 to 1.0)
        public readonly float $entropy,         // Order disorder (0.0 = perfect order, 1.0 = maximum chaos)
        public readonly float $energy,          // Energy density from wave engine
        public readonly float $causality,       // Causality tension
        public readonly float $strain,          // Structural strain
        public readonly float $stability,       // Overall stability gradient
        public readonly string $currentAttractor,
        public readonly int $year,
        public readonly ?string $currentIncarnationId = null,
        public readonly ?array $morphTargetCentroid = null,
        public readonly ?int $morphStartTick = null,
        public readonly float $morphIntensity = 1.0
    ) {
        $this->validate();
    }

    /**
     * Create the default initial observation state.
     * This is NOT "year 0" â€” it's just the default entry point for observation.
     */
    public static function defaultObservation(int $year = 0): self
    {
        return new self(
            order: 0.80,
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
     * @param float $entropyFeedback Entropy leak from civilization (0.0 to 1.0)
     * @return self The next cosmic state
     */
    public function evolve(PhysicalLaws $laws, float $externalEnergy, float $civilizationResonance = 0.0, int $deltaYears = 100, float $entropyFeedback = 0.0): self
    {
        // Initial values
        $ent = $this->entropy;
        $en = $externalEnergy; 
        $cau = $this->causality;
        $str = $this->strain;
        $stab = $this->stability;

        $alpha = $laws->entropyRate; 
        $beta = $laws->energyDecayRate * 0.015; // Scaled for stability
        $strainFeedbackFactor = 0.002 * $laws->causalityStrength; 

        $attractorService = new \WorldOS\Domains\Evolution\Services\AttractorService();
        $civSnapshot = CivilizationSnapshot::defaultObservation(); // Placeholder for actual pull context if needed

        // Internal Loop for Stability (dt = 1 year)
        for ($i = 0; $i < $deltaYears; $i++) {
            // Cosmic Attractor Logic (Simplified for now)
            $targetOrder = ($this->currentAttractor === 'EQUILIBRIUM') ? 0.8 : 0.4;
            $targetEntropy = ($this->currentAttractor === 'EQUILIBRIUM') ? 0.2 : 0.7;

            // 1. Order: entropy opposes order + attractor pull
            $dOrder = 0.01 * (1.0 - $ent) - 0.01 * $ent * $this->order + ($targetOrder - $this->order) * 0.02;
            $this_order = $this->order + tanh($dOrder); // Use tanh for stability

            // 2. Entropy: natural drift up slightly + civilization feedback
            $civEntropyLeak = $entropyFeedback * 0.005; // Feedback K
            $dEntropy = ($alpha * $ent * (1.0 - $ent)) - ($beta * $en) + 0.005 * (1.0 - $ent) - 0.005 * $stab * $ent + ($targetEntropy - $ent) * 0.02 + $civEntropyLeak;
            $ent = max(0.0, min(1.0, $ent + tanh($dEntropy)));

            // 3. Stability (derived from order and entropy)
            $stab = $this_order * (1.0 - $ent);

            // 4. Causality
            $cauChange = ($ent * 0.0003) + ($en * 0.0002) - 0.0001;
            $cau = max(0.0, min(2.0, $cau + tanh($cauChange)));

            // 5. Strain
            $strainRunaway = $strainFeedbackFactor * $ent * $str;
            $strainResonance = ($civilizationResonance * 0.0005) + ($entropyFeedback * 0.001);
            $strainRecovery = $stab * 0.0002;

            $strChange = $strainRunaway + $strainResonance - $strainRecovery;
            if ($str < 0.01 && $ent > 0.5) {
                $strChange += 0.0001; 
            }
            $str = max(0.0, min(2.0, $str + tanh($strChange)));
            
            // New order for next loop step
            // $this_order preserved if we want simple iteration
        }

        // Check for Fracture (Collapse Condition)
        // NOT resetting here. Just clamping or marking. 
        // BifurcationManager will handle the actual regime shift.
        $newAttractor = $this->currentAttractor;
        
        // Return new state
        return new self(
            order: round($this_order, 6),
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
     * Calculate the "cosmic tension" â€” a composite metric for narrative rendering.
     */
    public function cosmicTension(): float
    {
        return ($this->entropy * 0.3 + $this->causality * 0.2 + $this->strain * 0.3 + (1.0 - $this->order) * 0.2);
    }

    public function toArray(): array
    {
        return [
            'order' => $this->order,
            'entropy' => $this->entropy,
            'energy' => $this->energy,
            'causality' => $this->causality,
            'strain' => $this->strain,
            'stability' => $this->stability,
            'current_attractor' => $this->currentAttractor,
            'year' => $this->year,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            order: (float) ($data['order'] ?? 0.8),
            entropy: (float) $data['entropy'],
            energy: (float) $data['energy'],
            causality: (float) $data['causality'],
            strain: (float) $data['strain'],
            stability: (float) $data['stability'],
            currentAttractor: $data['current_attractor'] ?? 'EQUILIBRIUM',
            year: (int) $data['year']
        );
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


