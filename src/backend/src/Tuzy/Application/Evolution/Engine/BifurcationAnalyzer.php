<?php

declare(strict_types=1);

namespace Tuzy\Application\Evolution\Engine;

use Tuzy\Application\Cosmology\Entities\WorldStateVector;
use Tuzy\Domain\Evolution\EvolutionContext;
use Tuzy\Domain\Evolution\ValueObject\BranchEvent;
use Tuzy\Domain\Evolution\ValueObjects\VectorForce;

/**
 * BifurcationAnalyzer - Probability-based branch detection from curvature, divergence, and force.
 * High curvature + low legitimacy + high inequality -> sigmoid(chaosIndex) increases branch probability.
 */
final class BifurcationAnalyzer implements BifurcationAnalyzerInterface
{
    private const CURVATURE_THRESHOLD = 0.15;
    private const LEGITIMACY_LOW = 0.35;
    private const INEQUALITY_HIGH = 0.6;
    private const CHAOS_SCALE = 10.0;

    public function analyze(
        WorldStateVector $state,
        WorldStateVector $prevState,
        VectorForce $netForce,
        EvolutionContext $context
    ): ?BranchEvent {
        $curvature = $state->curvature($prevState);
        $divergence = $state->divergence();
        $legitimacy = $state->getLegitimacy();
        $inequality = $state->getInequality();
        $forceMag = $netForce->magnitude();

        $chaosIndex = 0.0;
        if ($curvature > self::CURVATURE_THRESHOLD) {
            $chaosIndex += $curvature * 2.0;
        }
        if ($legitimacy < self::LEGITIMACY_LOW) {
            $chaosIndex += (self::LEGITIMACY_LOW - $legitimacy) * 1.5;
        }
        if ($inequality > self::INEQUALITY_HIGH) {
            $chaosIndex += ($inequality - self::INEQUALITY_HIGH) * 1.0;
        }
        $chaosIndex += $divergence * 0.5;
        $chaosIndex += min(0.5, $forceMag * 2.0);

        $probability = 1.0 / (1.0 + exp(-self::CHAOS_SCALE * ($chaosIndex - 0.5)));

        if ($probability < 0.3) {
            return null;
        }

        if (mt_rand(1, 100) / 100.0 > $probability) {
            return null;
        }

        return new BranchEvent(
            type: 'DYNAMIC_STRESS',
            reason: sprintf('curvature=%.3f legitimacy=%.3f inequality=%.3f', $curvature, $legitimacy, $inequality),
            chaosIndex: $chaosIndex,
            metadata: [
                'curvature' => $curvature,
                'divergence' => $divergence,
                'probability' => $probability,
            ]
        );
    }
}
