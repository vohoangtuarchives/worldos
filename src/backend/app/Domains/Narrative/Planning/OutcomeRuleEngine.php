<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use Tuzy\Domain\Conflict\ValueObject\ConflictSeed;

/**
 * Computes default outcome (win/lose/partial) from arc type and conflict context.
 * Probabilistic but deterministic given deterministicSeed (e.g. arc_id hash).
 */
class OutcomeRuleEngine
{
    /**
     * @param array{influence?: float, cohesion?: float, instability?: float, alliance_weight?: float} $context
     */
    public function defaultOutcome(
        ArcType $arcType,
        ConflictSeed $dominantSeed,
        array $context = [],
        ?string $deterministicSeed = null,
    ): DefaultOutcome {
        $influence = $context['influence'] ?? 0.5;
        $cohesion = $context['cohesion'] ?? 0.5;
        $instability = $context['instability'] ?? 0.5;
        $allianceWeight = $context['alliance_weight'] ?? 0.5;

        $intensity = $dominantSeed->intensity;
        $stability = $dominantSeed->stability;

        $score = $influence * 0.3 + (1.0 - $instability) * 0.25 + $cohesion * 0.2 + $allianceWeight * 0.25;
        $score = max(0.0, min(1.0, $score));

        $volatileBonus = $stability === ConflictSeed::STABILITY_VOLATILE ? 0.15 : 0.0;
        $effectiveScore = $score + $volatileBonus;

        $hash = $deterministicSeed !== null ? abs(crc32($deterministicSeed)) % 1000 / 1000.0 : 0.5;
        $thresholdWin = 0.55 - $hash * 0.1;
        $thresholdPartial = 0.35 + $hash * 0.1;

        $result = DefaultOutcome::RESULT_LOSE;
        if ($effectiveScore >= $thresholdWin) {
            $result = DefaultOutcome::RESULT_WIN;
        } elseif ($effectiveScore >= $thresholdPartial) {
            $result = DefaultOutcome::RESULT_PARTIAL;
        }

        $scope = $intensity >= 0.7 ? DefaultOutcome::SCOPE_NATIONAL : DefaultOutcome::SCOPE_LOCAL;
        if ($intensity >= 0.9) {
            $scope = DefaultOutcome::SCOPE_GLOBAL;
        }

        return new DefaultOutcome($result, $intensity, $scope);
    }
}
