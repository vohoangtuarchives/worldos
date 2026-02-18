<?php

namespace App\Domains\Cosmology\Aggregates;

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Services\BasePhysicsEngine;
use App\Domains\Cosmology\Services\StructuralMutationEngine;
use Illuminate\Support\Collection;

class FieldSpace
{
    /** @var Collection<string, Universe> */
    protected Collection $universes;

    public function __construct(
        private ?StructuralMutationEngine $mutationEngine = null
    ) {
        $this->universes = new Collection();
        $this->mutationEngine = $this->mutationEngine ?? new StructuralMutationEngine();
    }

    public function addUniverse(Universe $universe): void
    {
        $this->universes->put($universe->getId(), $universe);
    }

    public function getUniverse(string $id): ?Universe
    {
        return $this->universes->get($id);
    }

    public function getAllUniverses(): Collection
    {
        return $this->universes;
    }

    /**
     * Evolve all universes in this space (physics + collapse). Internal/legacy when Universe has no world_id.
     */
    public function evolve(BasePhysicsEngine $kernel): void
    {
        $this->universes->each(function (Universe $universe) use ($kernel) {
            $universe->evolve($kernel);
            $signal = $kernel->getLastPhaseSignal();
            if ($signal !== null && $signal->shouldCollapse) {
                $nextState = $this->mutationEngine->mutate($universe->getState(), $signal->pressure);
                $universe->setState($nextState);
            }
        });
    }
}
