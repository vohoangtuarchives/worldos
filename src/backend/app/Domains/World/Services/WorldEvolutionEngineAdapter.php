<?php

namespace App\Domains\World\Services;

use App\Domains\Cosmology\Entities\Universe as CosmologyUniverse;
use App\Domains\Evolution\Kernel\WorldEvolutionKernel;
use Tuzy\Domain\Saga\ValueObject\ShockParams;
use App\Domains\World\Contracts\EvolutionEngineInterface;
use App\Models\UniverseModel;
use App\Models\World;

/**
 * Adapts World (aggregate root) evolution to Universe (runtime instance).
 * Tick Universe via WorldEvolutionKernel when universe has world_id; maps World ↔ Universe at boundary.
 * Phase 4.2: Optional ShockParams passed to kernel for Saga-mode shock.
 */
class WorldEvolutionEngineAdapter implements EvolutionEngineInterface
{
    public function __construct(
        private WorldEvolutionKernel $worldEvolutionKernel
    ) {
    }

    public function applyTick(object $runtimeInstance, ?object $shockParams = null): void
    {
        if (!$runtimeInstance instanceof CosmologyUniverse) {
            return;
        }
        $model = UniverseModel::find($runtimeInstance->getId());
        if ($model && $model->world_id) {
            $world = World::find($model->world_id);
            if ($world) {
                $shock = $shockParams instanceof ShockParams ? $shockParams : null;
                $this->worldEvolutionKernel->tickUniverse($world, $runtimeInstance, $shock);
                return;
            }
        }
        // No world_id: cannot use World kernel; caller should use Cosmology tick path
    }
}
