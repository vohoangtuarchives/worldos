<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Influences;

use App\WorldOS\Attractor\Entities\AttractorEntity;
use App\WorldOS\Influence\Contracts\EvolutionInfluenceInterface;
use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * Attractor Influence — gravitational pull toward civilizational basin.
 *
 * Active attractors pull the state vector toward their basin conditions.
 * The force direction depends on the attractor type and current proximity.
 *
 * Pure computation.
 */
final class AttractorInfluence implements EvolutionInfluenceInterface
{
    private const MAX_DELTA = 0.03;

    public function apply(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $attractors = $context->activeAttractors;

        if (empty($attractors)) {
            return VectorForce::zero();
        }

        $combined = VectorForce::zero();

        foreach ($attractors as $attractor) {
            $force = $this->calculateAttractorPull($attractor, $state);
            $combined = $combined->combine($force);
        }

        return $combined;
    }

    public function name(): string
    {
        return 'AttractorInfluence';
    }

    private function calculateAttractorPull(AttractorEntity $attractor, WorldStateVector $state): VectorForce
    {
        $pull = $attractor->getCurrentPull();

        if ($pull <= 0.0) {
            return VectorForce::zero();
        }

        $conditions = $attractor->getType()->basinConditions();
        $deltas = [];

        // For each dimension in basin conditions, pull toward the center of the range
        foreach ($conditions as $dimension => $range) {
            $current = $this->extractDimension($state, $dimension);
            $target = ($range['min'] + $range['max']) / 2.0;
            $delta = ($target - $current) * $pull * self::MAX_DELTA;
            $deltas[$dimension] = $delta;
        }

        return new VectorForce(
            deltaEntropy: $deltas['entropy'] ?? 0.0,
            deltaOrder: $deltas['order'] ?? 0.0,
            deltaCohesion: $deltas['cohesion'] ?? 0.0,
            deltaInnovation: $deltas['innovation'] ?? 0.0,
            deltaInequality: $deltas['inequality'] ?? 0.0,
            deltaLegitimacy: $deltas['legitimacy'] ?? 0.0,
            deltaTrauma: $deltas['trauma'] ?? 0.0,
            source: 'attractor:' . $attractor->getType()->value,
        );
    }

    private function extractDimension(WorldStateVector $state, string $dimension): float
    {
        return match ($dimension) {
            'entropy' => $state->entropy,
            'order' => $state->order,
            'cohesion' => $state->cohesion,
            'innovation' => $state->innovation,
            'inequality' => $state->inequality,
            'legitimacy' => $state->legitimacy,
            'trauma' => $state->trauma,
            default => 0.0,
        };
    }
}
