<?php

declare(strict_types=1);

namespace Tuzy\Domain\Runtime\ValueObject;

final readonly class UniverseMetrics
{
    public function __construct(
        public float $entropyTrend,
        public float $complexityIndex,
        public float $factionDiversity,
        public float $conflictDensity,
        public float $stabilityScore,
        public float $noveltyIndex,
        public float $mutationRate,
        public float $collapseRisk,
        public ?string $archetype = null,
        public ?string $dominantFactionType = null,
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
