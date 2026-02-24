<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

/**
 * HeroState — The immutable snapshot of a projected character.
 * 
 * Upgraded to wrap the 8D HeroStateVector dynamical system.
 * The HeroCyclePhase is no longer a stored state, but a derived interpretation.
 */
final class HeroState
{
    private function __construct(
        private readonly HeroStateVector $vector
    ) {
    }

    public static function genesis(HeroProfile $profile): self
    {
        return new self(HeroStateVector::genesis($profile));
    }

    public static function restore(HeroStateVector $vector): self
    {
        return new self($vector);
    }

    public function getVector(): HeroStateVector
    {
        return $this->vector;
    }

    /**
     * Helper accessors mapping the old API dynamically from the 8D vector
     */
    public function getStressLevel(): float
    {
        return $this->vector->get(HeroStateVector::DIM_STRESS);
    }

    public function getConviction(): float
    {
        return $this->vector->get(HeroStateVector::DIM_CONVICTION);
    }

    public function getTraumaIntensity(): float
    {
        return $this->vector->get(HeroStateVector::DIM_TRAUMA);
    }

    /**
     * Phase is now an Interpretation Layer (Derived Classification), not a Transition State.
     */
    public function getCyclePhase(): HeroCyclePhase
    {
        $stress     = $this->getStressLevel();
        $conviction = $this->getConviction();
        
        if ($stress < 0.3) {
            return HeroCyclePhase::ACCUMULATION; // Stable basin
        }
        
        if ($stress > 0.8) {
            return HeroCyclePhase::COLLAPSE; // Chaos/Failure basin
        }
        
        // Mid-range stress can be Strain, but if Conviction > Stress heavily, it's a Breakthrough
        // or Restabilization phase.
        if ($conviction > 0.8 && $stress < 0.6) {
            return HeroCyclePhase::RESTABILIZATION;
        }
        
        if ($stress > 0.6 && $conviction > 0.85) {
            // Highly strained but immense conviction -> Breakthrough edge
            return HeroCyclePhase::BREAKTHROUGH;
        }
        
        return HeroCyclePhase::STRAIN;
    }

    public function toArray(): array
    {
        return [
            'phase'  => $this->getCyclePhase()->value, // derived projection for UI
            'vector' => $this->vector->toArray(),
        ];
    }
}
