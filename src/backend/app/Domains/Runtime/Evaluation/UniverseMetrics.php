<?php

declare(strict_types=1);

namespace App\Domains\Runtime\Evaluation;

final class UniverseMetrics
{
    public function __construct(
        public readonly float $entropyTrend,
        public readonly float $complexityIndex,
        public readonly float $factionDiversity,
        public readonly float $conflictDensity,
        public readonly float $stabilityScore,
        public readonly float $noveltyIndex,
        public readonly float $mutationRate,
        public readonly float $collapseRisk,
        public readonly ?string $archetype = null,
        public readonly ?string $dominantFactionType = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'entropy_trend' => $this->entropyTrend,
            'complexity_index' => $this->complexityIndex,
            'faction_diversity' => $this->factionDiversity,
            'conflict_density' => $this->conflictDensity,
            'stability_score' => $this->stabilityScore,
            'novelty_index' => $this->noveltyIndex,
            'mutation_rate' => $this->mutationRate,
            'collapse_risk' => $this->collapseRisk,
            'archetype' => $this->archetype,
            'dominant_faction_type' => $this->dominantFactionType,
        ];
    }
}
