<?php

namespace App\Domains\Cosmology\Services;

use App\Domains\Cosmology\Entities\Universe;
use App\Domains\Cosmology\Entities\WorldStateVector;
use App\Domains\Cosmology\Mathematics\CriticalityDetector;
use App\Domains\Cosmology\Mathematics\PressureAccumulationField;
use App\Domains\Cosmology\Repositories\CosmologyRepository;
use App\Models\UniverseModel;
use Illuminate\Support\Str;

class LifecycleService
{
    private CosmologyRepository $repository;
    private SagaGeneratorService $sagaGenerator;
    private ArtifactService $artifactService;
    private CriticalityDetector $criticalityDetector;

    public function __construct(
        CosmologyRepository $repository,
        SagaGeneratorService $sagaGenerator,
        ArtifactService $artifactService,
        ?CriticalityDetector $criticalityDetector = null
    ) {
        $this->repository = $repository;
        $this->sagaGenerator = $sagaGenerator;
        $this->artifactService = $artifactService;
        $this->criticalityDetector = $criticalityDetector ?? new CriticalityDetector(new PressureAccumulationField());
    }

    public function checkDeath(Universe $universe): ?string
    {
        $state = $universe->getState();

        // Condition 0: Structural Fracture (đạo rạn nứt)
        // contradiction_index cao + innovation thấp + resource_flow = 0
        $assessment = $this->criticalityDetector->assess($state);
        if ($assessment['should_collapse']) {
            return 'STRUCTURAL_FRACTURE';
        }

        // Condition 1: Heat Death (Max Entropy)
        if ($state->getEntropy() > 0.95) {
            return 'HEAT_DEATH';
        }

        // Condition 2: Totalitarian Stagnation (Zero Entropy, Max Order)
        if ($state->getOrder() > 0.99 && $state->getEntropy() < 0.01) {
            return 'STAGNATION';
        }

        return null;
    }

    public function archive(Universe $universe, string $cause): void
    {
        $model = UniverseModel::find($universe->getId());
        if ($model) {
            $model->is_archived = true;
            $model->death_cause = $cause;
            
            // Generate Saga String for Universe table
            $sagaString = $this->sagaGenerator->generateSaga($universe, $cause);
            $model->saga = $sagaString;
            
            $model->save();

            // Create real Saga Project record (Phase 24 Integration)
            $sagaRecord = \App\Domains\Saga\Saga::create([
                'name' => "Final Chronicle of " . $model->name,
                'description' => $sagaString,
                'status' => 'completed',
                'world_count' => 1,
                'genre' => 'epitaph'
            ]);

            \App\Domains\Saga\SagaWorld::create([
                'saga_id' => $sagaRecord->id,
                'world_id' => $model->id,
                'status' => 'COLLAPSED',
                'sequence' => 1
            ]);

            // Generate Artifact (Phase 25)
            $this->artifactService->generateFromUniverse($model, "Collapse due to $cause");
        }
    }

    /**
     * Spawn a new Universe belonging to the given World. world_id is required (no standalone Universe).
     *
     * @param int $worldId Existing World id (must exist in worlds table)
     */
    public function spawnNew(int $worldId): Universe
    {
        if (!\App\Models\World::where('id', $worldId)->exists()) {
            throw new \InvalidArgumentException('spawnNew requires an existing World. world_id must exist in worlds table.');
        }

        $vector = WorldStateVector::create(
            mt_rand(0, 50) / 100,
            mt_rand(80, 100) / 100,
            mt_rand(0, 100) / 100,
            0.5,
            0.8,
            0.1,
            0.0,
            0.0
        );

        $id = (string) Str::uuid();
        $coords = [
            'x' => mt_rand(-1000, 1000),
            'y' => mt_rand(-1000, 1000),
            'z' => mt_rand(-1000, 1000),
        ];

        $universe = new Universe($vector, [], $id, 0, $coords);
        $this->repository->save($universe, $worldId);

        return $universe;
    }
}
