<?php

namespace Tuzy\Domain\Evolution\Entity;

class DriftOrchestrator
{
    public function applyDrift(WorldState $state, Tension $tension): void
    {
        $state->addTension($tension);
    }
    
    public function applyIntervention(WorldState $state, string $type, float $intensity): void
    {
        // Miracle reduces entropy (negative tension impact), Catastrophe increases it
        $tensionLevel = $type === 'tension' ? abs($intensity) : -abs($intensity);
        $source = $type === 'tension' ? 'god_catastrophe' : 'god_miracle';

        $tension = new Tension(
            domain: 'divine_intervention',
            level: $tensionLevel,
            source: $source
        );

        $state->addTension($tension);
    }
}

