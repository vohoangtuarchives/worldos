<?php

declare(strict_types=1);

namespace App\Domains\Narrative\Planning;

use WorldOS\Legacy\Domain\Conflict\ValueObject\ConflictSeed;

/**
 * Maps ConflictSeed[] to primary ArcType for story structure.
 * Picks the dominant conflict type by intensity; deterministic.
 */
class ArcSelector
{
    /**
     * Select one arc type from seeds. Uses highest-intensity seed; tie-break by priority order.
     *
     * @param list<ConflictSeed> $seeds
     */
    public function select(array $seeds): ?ArcType
    {
        if ($seeds === []) {
            return null;
        }

        $byType = [];
        foreach ($seeds as $seed) {
            $byType[$seed->type] = max($byType[$seed->type] ?? 0.0, $seed->intensity);
        }

        // Priority order for tie-break: rebellion > class_struggle > institutional_fragility > elite_power_consolidation
        $typeToArc = [
            ConflictSeed::TYPE_REBELLION_POTENTIAL => ArcType::REBELLION,
            ConflictSeed::TYPE_CLASS_STRUGGLE => ArcType::REBELLION,
            ConflictSeed::TYPE_INSTITUTIONAL_FRAGILITY => ArcType::RISE_AND_FALL,
            ConflictSeed::TYPE_ELITE_POWER_CONSOLIDATION => ArcType::POWER_CONSOLIDATION,
        ];

        $bestIntensity = 0.0;
        $bestArc = null;
        $priorityOrder = [
            ConflictSeed::TYPE_REBELLION_POTENTIAL,
            ConflictSeed::TYPE_CLASS_STRUGGLE,
            ConflictSeed::TYPE_INSTITUTIONAL_FRAGILITY,
            ConflictSeed::TYPE_ELITE_POWER_CONSOLIDATION,
        ];

        foreach ($priorityOrder as $type) {
            $intensity = $byType[$type] ?? 0.0;
            if ($intensity > $bestIntensity && isset($typeToArc[$type])) {
                $bestIntensity = $intensity;
                $bestArc = $typeToArc[$type];
            }
        }

        return $bestArc;
    }

    /**
     * Select arc type and return the dominant seed (for downstream RuleEngine).
     *
     * @param list<ConflictSeed> $seeds
     * @return array{arc_type: ArcType|null, dominant_seed: ConflictSeed|null}
     */
    public function selectWithDominant(array $seeds): array
    {
        if ($seeds === []) {
            return ['arc_type' => null, 'dominant_seed' => null];
        }

        $typeToArc = [
            ConflictSeed::TYPE_REBELLION_POTENTIAL => ArcType::REBELLION,
            ConflictSeed::TYPE_CLASS_STRUGGLE => ArcType::REBELLION,
            ConflictSeed::TYPE_INSTITUTIONAL_FRAGILITY => ArcType::RISE_AND_FALL,
            ConflictSeed::TYPE_ELITE_POWER_CONSOLIDATION => ArcType::POWER_CONSOLIDATION,
        ];

        $bestSeed = null;
        $bestIntensity = 0.0;
        foreach ($seeds as $seed) {
            if (!isset($typeToArc[$seed->type])) {
                continue;
            }
            if ($seed->intensity > $bestIntensity) {
                $bestIntensity = $seed->intensity;
                $bestSeed = $seed;
            }
        }

        $arcType = $bestSeed ? $typeToArc[$bestSeed->type] : null;
        return ['arc_type' => $arcType, 'dominant_seed' => $bestSeed];
    }
}
