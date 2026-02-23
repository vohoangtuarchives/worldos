<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Application\Evolution\Adapter;

use WorldOS\Legacy\Domain\Cosmology\ValueObject\CivilizationState;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\CosmicState;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\EnvironmentState;
use WorldOS\Legacy\Domain\Cosmology\ValueObject\WorldSnapshot;
use WorldOS\Legacy\Application\Cosmology\Entities\WorldStateVector;

/**
 * VectorToSnapshotAdapter - Converts WorldStateVector back to WorldSnapshot for persistence.
 * Used at save boundary only. Environment and social classes preserved from previous or default.
 */
final class VectorToSnapshotAdapter
{
    public function toSnapshot(WorldStateVector $vector, int $year, ?WorldSnapshot $previous = null): WorldSnapshot
    {
        $cosmic = $this->vectorToCosmic($vector, $year, $previous?->cosmic);
        $environment = $previous?->environment ?? EnvironmentState::defaultObservation($year);
        $civilization = $this->vectorToCivilization($vector, $year, $previous?->civilization);

        return new WorldSnapshot($cosmic, $environment, $civilization, $year);
    }

    private function vectorToCosmic(WorldStateVector $v, int $year, ?CosmicState $prev): CosmicState
    {
        $entropy = $v->getEntropy();
        return new CosmicState(
            entropy: $entropy,
            energy: $prev?->energy ?? 0.6,
            causality: $prev?->causality ?? 0.3,
            strain: $prev?->strain ?? 0.05,
            stability: 1.0 - $entropy,
            currentAttractor: $prev?->currentAttractor ?? 'EQUILIBRIUM',
            year: $year
        );
    }

    private function vectorToCivilization(WorldStateVector $v, int $year, ?CivilizationState $prev): CivilizationState
    {
        $innovation = $v->getInnovation();
        $stability = $v->getLegitimacy();
        return new CivilizationState(
            culturalEnergy: $innovation,
            spiritualCohesion: $v->getCohesion(),
            technologicalLevel: min(2.0, $innovation * 1.5),
            stability: $stability,
            prosperity: $v->getResourceStock(),
            militaryPressure: $v->getMilitary(),
            externalThreat: $prev?->externalThreat ?? 0.05,
            internalEntropy: $v->getInequality(),
            resonanceAccumulator: $prev?->resonanceAccumulator ?? 0.0,
            resilience: $prev?->resilience ?? 1.0,
            year: $year,
            yearsInPhase: $prev?->yearsInPhase ?? 0,
            socialClasses: $prev?->socialClasses ?? CivilizationState::defaultObservation($year)->socialClasses
        );
    }
}
