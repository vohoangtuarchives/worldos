<?php

declare(strict_types=1);

namespace App\WorldOS\Influence\Services;

use App\WorldOS\Influence\Contracts\EvolutionInfluenceInterface;
use App\WorldOS\Influence\ValueObjects\EvolutionContext;
use App\WorldOS\Influence\ValueObjects\VectorForce;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;

/**
 * InfluencePipeline — aggregates all evolution influences.
 *
 * From docs §16.4: Pipeline gọi từng influence và aggregate.
 * Order: Structural → Cultural → External → Narrative → Meta
 *
 * Each influence independently calculates its VectorForce;
 * the pipeline combines them into a single aggregate force.
 */
final class InfluencePipeline
{
    /** @var EvolutionInfluenceInterface[] */
    private array $influences = [];

    /**
     * Register an influence with priority (lower = runs first).
     */
    public function register(EvolutionInfluenceInterface $influence): self
    {
        $this->influences[] = $influence;

        return $this;
    }

    /**
     * Run all influences and return the combined VectorForce.
     *
     * @return VectorForce Aggregate force from all influences
     */
    public function run(WorldStateVector $state, EvolutionContext $context): VectorForce
    {
        $combined = VectorForce::zero();

        foreach ($this->influences as $influence) {
            $force = $influence->apply($state, $context);
            $combined = $combined->combine($force);
        }

        return $combined;
    }

    /**
     * Run all influences and return individual results (for debugging/audit).
     *
     * @return array<string, VectorForce> influence name → force
     */
    public function runDetailed(WorldStateVector $state, EvolutionContext $context): array
    {
        $results = [];

        foreach ($this->influences as $influence) {
            $results[$influence->name()] = $influence->apply($state, $context);
        }

        return $results;
    }

    /**
     * @return EvolutionInfluenceInterface[]
     */
    public function getInfluences(): array
    {
        return $this->influences;
    }
}
