<?php

namespace App\Domains\Saga\Actions;

use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Domains\Evolution\Kernel\WorldEvolutionKernel;
use App\Models\UniverseModel;
use App\Models\World;

class ApplySelectionPressureAction
{
    public function __construct(
        private CosmologyRepository $cosmologyRepository,
        private WorldEvolutionKernel $kernel
    ) {}

    public function execute(string $universeId, string $type, float $intensity): void
    {
        $universe = $this->cosmologyRepository->find($universeId);
        if (!$universe) {
            throw new \InvalidArgumentException("Universe not found: {$universeId}");
        }

        $model = UniverseModel::findOrFail($universeId);
        $world = $model->world_id ? World::find($model->world_id) : null;

        // Optional validation against World laws if needed
        // if ($world && !$this->kernel->validateMutation($world, new MutationSuggestion($type, $intensity))) { ... }

        $this->kernel->applyPressure($universe, $type, $intensity);
        
        // Save back to DB
        $this->cosmologyRepository->save($universe, $model->world_id);
    }
}
