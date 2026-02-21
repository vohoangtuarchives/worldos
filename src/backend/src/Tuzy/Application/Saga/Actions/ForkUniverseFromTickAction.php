<?php

namespace Tuzy\Application\Saga\Actions;

use Tuzy\Infrastructure\Cosmology\Repositories\CosmologyRepository;
use Tuzy\Application\Saga\Services\SagaService;
use App\Models\UniverseModel;
use Tuzy\Domain\Saga\SagaWorld;

class ForkUniverseFromTickAction
{
    public function __construct(
        private SagaService $sagaService,
        private CosmologyRepository $cosmologyRepository
    ) {}

    public function execute(string $universeId, int $tick, ?string $sagaId = null): UniverseModel
    {
        $universe = $this->cosmologyRepository->find($universeId);
        if (!$universe) {
            throw new \InvalidArgumentException("Universe not found: {$universeId}");
        }

        // Use the existing fork logic in SagaService
        $forkedUniverse = $this->sagaService->fork($universe, $tick);
        $newId = $forkedUniverse->getId();

        $forkedModel = UniverseModel::findOrFail($newId);

        // If sagaId is provided, register this new universe in the saga
        if ($sagaId) {
            $lastSequence = SagaWorld::where('saga_id', $sagaId)->max('sequence') ?? 0;
            SagaWorld::create([
                'saga_id' => $sagaId,
                'universe_id' => $newId,
                'world_id' => $forkedModel->world_id,
                'status' => SagaWorld::STATUS_PENDING,
                'sequence' => $lastSequence + 1,
            ]);
        }

        return $forkedModel;
    }
}
