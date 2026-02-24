<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Hero\ValueObject;

use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * UniverseProjectionMatrix (P) 8x17.
 * Projects the 17D macro Universe state into an 8D micro forcing vector for the Hero.
 * Weights are adjusted by the HeroProfile's characteristics (dominant dimension).
 */
final class UniverseProjectionMatrix
{
    /**
     * Projects the 17D universe into an 8D forcing vector.
     */
    public function project(StateVector $universe, HeroProfile $profile): HeroStateVector
    {
        $tension    = $universe->get(StateVector::DIMENSION_COSMIC_TENSION);
        $entropy    = $universe->get(StateVector::DIMENSION_ENTROPY);
        $stability  = max(0.0, 1.0 - (($tension + $entropy) / 2));
        $chaos      = 0.5; // Chaos Index was removed or missing, setting constant 0.5 for now
        
        // We use the dominant dimension as the primary affine forcing term for the hero's identity
        $dominantMacro = $universe->get($profile->getDominantDimension());

        // Base forcing mapped conceptually from the discussion
        // Using static weights, scaled down to keep spectral stability (norm of P * U < 0.5)
        
        $σ = (0.30 * $tension)    + (0.15 * $chaos) - (0.20 * $stability);
        $κ = (0.20 * $stability)  + (0.30 * $dominantMacro) - (0.30 * $entropy);
        $ρ = (0.30 * $stability)  + (0.10 * $dominantMacro) - (0.20 * $chaos);
        $α = (0.20 * $chaos)      + (0.10 * $dominantMacro);
        $φ = (0.35 * $entropy)    + (0.15 * $tension);
        $χ = (0.30 * $stability)  - (0.35 * $entropy) - (0.20 * $chaos);
        $ε = (0.20 * $dominantMacro) + (0.15 * $tension);
        $τ = (0.40 * $tension)    + (0.30 * $chaos) + (0.25 * $entropy);

        // Raw forcing logic without clamping (clamping happens via logistic bounding later)
        return HeroStateVector::createRaw([
            HeroStateVector::DIM_STRESS     => $σ,
            HeroStateVector::DIM_CONVICTION => $κ,
            HeroStateVector::DIM_RESILIENCE => $ρ,
            HeroStateVector::DIM_ADAPTATION => $α,
            HeroStateVector::DIM_FEAR       => $φ,
            HeroStateVector::DIM_CLARITY    => $χ,
            HeroStateVector::DIM_EGO        => $ε,
            HeroStateVector::DIM_TRAUMA     => $τ,
        ]);
    }
}
