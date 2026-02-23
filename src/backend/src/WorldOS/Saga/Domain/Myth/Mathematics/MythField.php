<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\Mathematics;

use WorldOS\Saga\Domain\Hero\ValueObject\HeroState;
use WorldOS\Saga\Domain\Hero\ValueObject\HeroStateVector;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;

/**
 * Core Law Layer for the Saga.
 * The MythField is not merely a theme; it acts as an underlying physical macro-field.
 */
final class MythField
{
    public function __construct(
        private readonly MythVector $vector,
    ) {
    }

    /**
     * Myth Field exerts forces on the Hero's psyche.
     */
    public function influenceHero(HeroState $hero): HeroState
    {
        $v = $hero->getVector()->toArray();

        $asc = $this->vector->get(MythVector::DIM_ASCENSION);
        $cor = $this->vector->get(MythVector::DIM_CORRUPTION);

        // Ascension field reduces fear, increases clarity
        $v[HeroStateVector::DIM_FEAR] = max(0.0, ($v[HeroStateVector::DIM_FEAR] ?? 0) - (0.02 * $asc));
        $v[HeroStateVector::DIM_CLARITY] = min(1.0, ($v[HeroStateVector::DIM_CLARITY] ?? 0) + (0.02 * $asc));

        // Corruption field increases ego & fear
        $v[HeroStateVector::DIM_EGO] = min(1.0, ($v[HeroStateVector::DIM_EGO] ?? 0) + (0.02 * $cor));
        $v[HeroStateVector::DIM_FEAR] = min(1.0, ($v[HeroStateVector::DIM_FEAR] ?? 0) + (0.01 * $cor));

        return HeroState::restore(HeroStateVector::fromArray($v));
    }

    /**
     * Myth Field exerts forces on the macro Universe's entropy and tension.
     */
    public function influenceUniverse(StateVector $u): StateVector
    {
        $data = $u->toArray();

        $entropy = $data[StateVector::DIMENSION_ENTROPY] ?? 0.0;
        $tension = $data[StateVector::DIMENSION_COSMIC_TENSION] ?? 0.0;

        $entropy += 0.03 * $this->vector->get(MythVector::DIM_CORRUPTION);
        $entropy -= 0.02 * $this->vector->get(MythVector::DIM_ASCENSION);
        $tension += 0.02 * $this->vector->get(MythVector::DIM_RECURSION);

        return $u
            ->withDimension(StateVector::DIMENSION_ENTROPY, max(0.0, min(1.0, $entropy)))
            ->withDimension(StateVector::DIMENSION_COSMIC_TENSION, max(0.0, min(1.0, $tension)));
    }

    public function vector(): MythVector
    {
        return $this->vector;
    }
}
