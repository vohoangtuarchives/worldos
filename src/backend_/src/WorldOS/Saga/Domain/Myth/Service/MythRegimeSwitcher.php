<?php

declare(strict_types=1);

namespace WorldOS\Saga\Domain\Myth\Service;

use WorldOS\Saga\Domain\Myth\Entity\MythRegimeState;
use WorldOS\Saga\Domain\Myth\ValueObject\MythRegime;
use WorldOS\Saga\Domain\Myth\ValueObject\MythVector;
use WorldOS\Saga\Domain\Saga\ValueObject\UniverseMacroMetrics;

final class MythRegimeSwitcher
{
    public function __construct(
        private readonly EntropyTurbulenceCalculator $turbulenceCalculator
    ) {
    }

    public function evaluate(
        MythVector $myth,
        UniverseMacroMetrics $metrics,
        MythRegimeState $state
    ): MythRegimeState {
        $dominant = $myth->getDominantDimension();
        $intensity = $myth->get($dominant);

        $turbulence = $this->turbulenceCalculator->compute($metrics);

        // Calculate force based on thermodynamic approach
        $switchForce = ($intensity * $turbulence) - $state->inertia - $state->basinDepth;

        // Probabilistic switching function using sigmoid
        $prob = 1.0 / (1.0 + exp(-$switchForce * $state->volatility));

        if ((mt_rand() / mt_getrandmax()) < $prob) {
            $newRegime = $this->mapToRegime($dominant);

            return new MythRegimeState(
                current: $newRegime,
                inertia: $state->inertia * 1.2, // Harden basin slightly on jump
                volatility: min(1.0, $state->volatility + 0.1), // Increase chaos on jump
                basinDepth: $this->basinDepthOf($newRegime)
            );
        }

        return $state;
    }

    private function mapToRegime(string $dim): MythRegime
    {
        return match ($dim) {
            MythVector::DIM_ASCENSION => MythRegime::ASCENSION_ERA,
            MythVector::DIM_CORRUPTION => MythRegime::CORRUPTION_ERA,
            MythVector::DIM_RECURSION => MythRegime::RECURSIVE_ERA,
            MythVector::DIM_ESCAPE => MythRegime::ESCAPE_ERA,
            MythVector::DIM_CONVERGENCE => MythRegime::CONVERGENCE_ERA,
            default => MythRegime::TRANSITIONAL,
        };
    }

    private function basinDepthOf(MythRegime $regime): float
    {
        return match ($regime) {
            MythRegime::ASCENSION_ERA => 0.6,
            MythRegime::CORRUPTION_ERA => 0.4,
            MythRegime::RECURSIVE_ERA => 0.8,
            MythRegime::ESCAPE_ERA => 0.3,
            MythRegime::CONVERGENCE_ERA => 0.9,
            MythRegime::TRANSITIONAL => 0.2,
        };
    }
}
