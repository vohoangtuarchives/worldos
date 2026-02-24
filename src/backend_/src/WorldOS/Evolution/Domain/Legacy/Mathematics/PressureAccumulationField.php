<?php

namespace WorldOS\Evolution\Domain\Legacy\Mathematics;

use WorldOS\Evolution\Domain\Legacy\ValueObject\WorldStateVector;
use WorldOS\Evolution\Domain\Legacy\ValueObject\ConstraintProfile;

/**
 * Pressure Accumulation Field (Ãp suáº¥t tÃ­ch lÅ©y)
 *
 * Contradiction (mÃ¢u thuáº«n) tÃ­ch lÅ©y thÃ nh Ã¡p suáº¥t.
 * Supports both legacy linear contradiction index and non-linear formula:
 *   BT = Î±Â·PowerImbalance + Î²Â·ResourceStress + Î³Â·IdeologyDivergence + Î´Â·SocialFragmentation
 *   FA = kÂ·BTÂ²  (Feedback Amplification)
 *   CIT = (PowerImbalanceÃ—ResourceStress) + (IdeologyDivergenceÃ—SocialFragmentation)
 *   Raw = BT + FA + CIT;  P(t) = Î»Â·P(t-1) + (1-Î»)Â·Raw  (inertia)
 */
class PressureAccumulationField
{
    protected float $inequalityWeight = 0.35;   // Structural tension
    protected float $traumaWeight = 0.35;        // Accumulated pain
    protected float $entropyWeight = 0.30;       // Disorder

    /** Contradiction-like index: derived from Cosmology dimensions (legacy) */
    public function contradictionIndex(WorldStateVector $s): float
    {
        $structuralTension = $s->getInequality() * (1.0 - $s->getLegitimacy());
        return min(1.0,
            $this->inequalityWeight * $structuralTension
            + $this->traumaWeight * $s->getTrauma()
            + $this->entropyWeight * $s->getEntropy()
        );
    }

    /** Accumulated pressure (monotonic increase when no release) â€” legacy */
    public function pressure(WorldStateVector $s, float $accumulatedPressure = 0.0): float
    {
        $delta = $this->contradictionIndex($s) * 0.05; // Per-tick accumulation
        return min(1.0, $accumulatedPressure + $delta);
    }

    /** Pressure release rate when innovation absorbs entropy (reorganization) */
    public function releaseRate(float $innovationRate): float
    {
        return $innovationRate * 0.15; // Innovation can dissipate pressure
    }

    // --- Non-linear pressure (0..1 components from state) ---

    public function powerImbalance(WorldStateVector $s): float
    {
        return min(1.0, $s->getInequality() * (1.0 - $s->getLegitimacy()));
    }

    public function resourceStress(WorldStateVector $s): float
    {
        return min(1.0, 1.0 - $s->getResourceStock());
    }

    public function ideologyDivergence(WorldStateVector $s): float
    {
        return min(1.0, 1.0 - $s->getCohesion());
    }

    public function socialFragmentation(WorldStateVector $s): float
    {
        return min(1.0, (1.0 - $s->getCohesion()) * 0.7 + $s->getEntropy() * 0.3);
    }

    /**
     * Raw pressure from non-linear formula (no inertia).
     * BT + FA + CIT, clamped [0, 1].
     */
    public function rawPressureNonLinear(WorldStateVector $s, ConstraintProfile $profile): float
    {
        $power = $this->powerImbalance($s);
        $resource = $this->resourceStress($s);
        $ideology = $this->ideologyDivergence($s);
        $social = $this->socialFragmentation($s);

        $bt = $profile->alpha() * $power
            + $profile->beta() * $resource
            + $profile->gamma() * $ideology
            + $profile->delta() * $social;

        $feedback = $profile->feedbackK() * $bt * $bt;
        $cit = ($power * $resource) + ($ideology * $social);

        $raw = $bt + $feedback + $cit;
        return max(0.0, min(1.0, $raw));
    }

    /**
     * Smoothed pressure with inertia: P(t) = Î»Â·P(t-1) + (1-Î»)Â·Raw.
     */
    public function pressureSmoothed(
        WorldStateVector $s,
        ConstraintProfile $profile,
        float $previousPressure
    ): float {
        $raw = $this->rawPressureNonLinear($s, $profile);
        $lambda = $profile->inertia();
        $p = $lambda * $previousPressure + (1.0 - $lambda) * $raw;
        return max(0.0, min(1.0, $p));
    }

    /**
     * Analysis snapshot: vector tráº¡ng thÃ¡i dÃ¹ng cho phÃ¢n tÃ­ch ngoÃ i (AI, dashboard).
     * Map dimensions WorldOS â†’ coherence, entropy, belief_mass, resource_flow, stability, innovation_rate, contradiction_index.
     *
     * @return array{coherence: float, entropy: float, belief_mass: float, resource_flow: float, stability: float, innovation_rate: float, contradiction_index: float}
     */
    public function getAnalysisSnapshot(WorldStateVector $s): array
    {
        $cohesion = $s->getCohesion();
        $legitimacy = $s->getLegitimacy();

        return [
            'coherence' => $cohesion,
            'entropy' => $s->getEntropy(),
            'belief_mass' => min(1.0, $cohesion * 0.6 + $legitimacy * 0.4), // Táº­p há»£p Ã½ thá»©c / niá»m tin há»‡ thá»‘ng
            'resource_flow' => $s->getResourceStock(), // Proxy: stock â‰ˆ capacity to flow
            'stability' => $s->getOrder(),
            'innovation_rate' => $s->getInnovation(),
            'contradiction_index' => $this->contradictionIndex($s),
        ];
    }
}


