<?php

namespace App\Domains\WorldEvolution\Entities;

use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\CoreTruth\ValueObjects\CoreTruth;
use App\Domains\Epistemology\ValueObjects\EpistemicIndex;

/**
 * WorldState represents the holistic state of a single Universe at a specific point in time.
 * It combines the cosmological state vector, the epistemic index, and the core truth.
 */
class WorldState
{
    public function __construct(
        public readonly string $sagaId,
        public readonly string $universeId,
        public readonly int $currentYear,
        public WorldStateVector $vector,
        public EpistemicIndex $epistemicIndex,
        public readonly CoreTruth $coreTruth
    ) {}

    public function applyVectorImpact(array $impacts): void
    {
        $newValues = [];
        foreach (WorldStateVector::dimensions() as $dim) {
            $current = $this->vector->get($dim);
            $change = $impacts[$dim] ?? 0.0;
            $newValues[$dim] = max(0.0, min(1.0, $current + $change));
        }
        $this->vector = WorldStateVector::fromArray($newValues);
    }

    public function applyEpistemicDrift(float $instabilityChange, float $clarityChange): void
    {
        $newInstability = max(0.0, min(1.0, $this->epistemicIndex->instability + $instabilityChange));
        $newClarity = max(0.0, min(1.0, $this->epistemicIndex->clarity + $clarityChange));
        
        $this->epistemicIndex = new EpistemicIndex($newInstability, $newClarity);
    }
}
