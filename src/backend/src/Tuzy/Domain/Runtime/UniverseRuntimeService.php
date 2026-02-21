<?php

namespace Tuzy\Domain\Runtime;

use Tuzy\Domain\Cosmology\Cosmology;
use Tuzy\Application\Cosmology\Entities\Universe as CosmologyUniverse;
use Tuzy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use Tuzy\Infrastructure\Cosmology\Repositories\UniverseSnapshotRepository;
use Tuzy\Domain\Runtime\Event\UniverseTicked;
use Tuzy\Domain\Saga\ValueObject\ShockParams;
use Tuzy\Application\Saga\Services\ShockInjector;
use Tuzy\Domain\World\Contracts\EvolutionEngineInterface;
use App\Models\UniverseModel;
use App\Models\World;
use App\Policies\UniverseRuntimePolicy;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Universe = Runtime Instance of a World.
 * Phase 3: Runtime depends only on EvolutionEngineInterface (no direct Cosmology/Evolution kernel).
 * Tick: if Universe has world_id, delegate to engine->applyTick(universe); else legacy Cosmology tick.
 * Phase 4.2: Optional sagaId/startYear for advance(); ShockInjector for shock during tick.
 */
class UniverseRuntimeService
{
    public function __construct(
        private CosmologyRepository $cosmologyRepository,
        private UniverseSnapshotRepository $universeSnapshotRepository,
        private UniverseRuntimePolicy $policy,
        private EvolutionEngineInterface $evolutionEngine,
        private ?ShockInjector $shockInjector = null
    ) {
    }

    /**
     * Advance universe by N ticks (e.g. for Saga simulateWorld). Returns the universe after the last tick.
     * If ticks <= 0, returns current universe without advancing.
     * Phase 4.2: Optional sagaId and startYear for ShockInjector (inject shock on interval years).
     */
    public function advance(string $universeId, int $ticks, ?int $sagaId = null, ?int $startYear = 0): CosmologyUniverse
    {
        if ($ticks <= 0) {
            $universe = $this->cosmologyRepository->find($universeId);
            if ($universe === null) {
                throw new \InvalidArgumentException("Universe not found: {$universeId}");
            }
            return $universe;
        }
        $universe = null;
        for ($i = 0; $i < $ticks; $i++) {
            $currentYear = $startYear + $i;
            $universe = $this->tick($universeId, $sagaId, $currentYear);
        }
        return $universe;
    }

    /**
     * Apply one tick to the universe (runtime instance).
     * When universe has world_id: WorldEvolutionKernel::tickUniverse (no Cosmology::tick).
     * Phase 4.2: Optional sagaId and currentYear; if set and ShockInjector says so, apply shock.
     */
    public function tick(string $universeId, ?int $sagaId = null, ?int $currentYear = null): CosmologyUniverse
    {
        $universe = $this->cosmologyRepository->findOrSeed($universeId);
        $model = UniverseModel::find($universeId);

        $shockParams = null;
        if ($sagaId !== null && $currentYear !== null && $this->shockInjector !== null) {
            if ($this->shockInjector->shouldInject($sagaId, $currentYear)) {
                $shockParams = new ShockParams(
                    $this->shockInjector->magnitude($sagaId, $currentYear),
                    $this->shockInjector->shockType($sagaId, $currentYear)
                );
            }
        }

        if ($model && $model->world_id) {
            $world = World::find($model->world_id);
            if ($world && !$this->policy->tickUniverse($model, $world)) {
                throw new AuthorizationException('Universe cannot tick: World is frozen (HALTED).');
            }
            if ($world) {
                $this->evolutionEngine->applyTick($universe, $shockParams);
                $this->cosmologyRepository->save($universe, $model->world_id);
                $this->universeSnapshotRepository->save($universe, []);
                $this->dispatchTicked($universe, $model->world_id);
                return $universe;
            }
        }

        // Universe without world_id (legacy): Cosmology tick (no shock)
        $cosmology = Cosmology::boot();
        $cosmology->getFieldSpace()->addUniverse($universe);
        $cosmology->tick();
        $this->cosmologyRepository->save($universe, $model?->world_id);
        $this->universeSnapshotRepository->save($universe, []);
        $this->dispatchTicked($universe, null);
        return $universe;
    }

    private function dispatchTicked(CosmologyUniverse $universe, ?string $worldId): void
    {
        $state = $universe->getState();
        event(new UniverseTicked(
            $universe->getId(),
            $worldId ? (string) $worldId : null,
            $universe->getAge(),
            [
                'order' => $state->getOrder(),
                'entropy' => $state->getEntropy(),
            ]
        ));
    }
}
