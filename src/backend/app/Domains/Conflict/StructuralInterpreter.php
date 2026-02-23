<?php

declare(strict_types=1);

namespace App\Domains\Conflict;

use WorldOS\Legacy\Domain\Conflict\ValueObject\ConflictSeed;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Mathematics\StressModel;

/**
 * Layer 2: maps structural state + derived indices → ConflictSeed[].
 * Deterministic; no LLM. Used as bridge between world and story.
 */
class StructuralInterpreter
{
    private const HIGH_INEQUALITY = 0.6;
    private const LOW_LEGITIMACY = 0.4;
    private const HIGH_ELITE_COHESION = 0.7;
    private const HIGH_PRESSURE = 0.5;
    private const HIGH_STRUCTURAL_ENTROPY = 0.6;

    public function __construct(
        private readonly StressModel $stressModel,
    ) {
    }

    /**
     * @param array<string, float>|null $influenceVector Optional attractor/influence weights per dimension
     * @return list<ConflictSeed>
     */
    public function interpret(
        WorldStateVector $state,
        float $pressureScore,
        ?array $influenceVector = null,
    ): array {
        $derived = $this->stressModel->getComponents($state);
        $inequality = $state->getInequality();
        $legitimacy = $state->getLegitimacy();
        $eliteCohesion = $state->getEliteCohesion();
        $economicStress = $derived['economic_stress'] ?? 0.0;
        $politicalStress = $derived['political_stress'] ?? 0.0;
        $structuralEntropy = $derived['structural_entropy'] ?? 0.0;

        $seeds = [];

        // class_struggle: high inequality + low legitimacy
        if ($inequality >= self::HIGH_INEQUALITY && $legitimacy <= self::LOW_LEGITIMACY) {
            $intensity = min(1.0, $inequality * (1.0 - $legitimacy) + $economicStress * 0.3);
            $stability = $pressureScore >= self::HIGH_PRESSURE ? ConflictSeed::STABILITY_VOLATILE : ConflictSeed::STABILITY_BUILDING;
            $seeds[] = ConflictSeed::classStruggle($intensity, $stability);
        }

        // elite_power_consolidation: high elite cohesion + low legitimacy
        if ($eliteCohesion >= self::HIGH_ELITE_COHESION && $legitimacy <= self::LOW_LEGITIMACY) {
            $intensity = min(1.0, $eliteCohesion * (1.0 - $legitimacy) + $politicalStress * 0.3);
            $stability = $structuralEntropy >= self::HIGH_STRUCTURAL_ENTROPY ? ConflictSeed::STABILITY_VOLATILE : ConflictSeed::STABILITY_STABLE;
            $seeds[] = ConflictSeed::elitePowerConsolidation($intensity, $stability);
        }

        // institutional_fragility: structural entropy + low cohesion
        $cohesion = $state->getCohesion();
        if ($structuralEntropy >= 0.4 && $cohesion <= 0.5) {
            $intensity = min(1.0, $structuralEntropy * 0.6 + (1.0 - $cohesion) * 0.4);
            $stability = $pressureScore >= self::HIGH_PRESSURE ? ConflictSeed::STABILITY_VOLATILE : ConflictSeed::STABILITY_BUILDING;
            $seeds[] = ConflictSeed::institutionalFragility($intensity, $stability);
        }

        // rebellion_potential: combination of inequality, trauma, low legitimacy
        $trauma = $state->getTrauma();
        $rebellionScore = $inequality * 0.4 + $trauma * 0.3 + (1.0 - $legitimacy) * 0.3;
        if ($rebellionScore >= 0.35) {
            $intensity = min(1.0, $rebellionScore + $pressureScore * 0.2);
            $stability = $pressureScore >= self::HIGH_PRESSURE ? ConflictSeed::STABILITY_VOLATILE : ConflictSeed::STABILITY_BUILDING;
            $seeds[] = ConflictSeed::rebellionPotential($intensity, $stability);
        }

        return $seeds;
    }

    /**
     * Convenience: interpret from state only, computing pressure from StressModel.
     */
    public function interpretFromState(WorldStateVector $state, ?array $influenceVector = null): array
    {
        $pressureScore = $this->stressModel->totalPressure($state);
        return $this->interpret($state, $pressureScore, $influenceVector);
    }
}
