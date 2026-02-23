<?php

declare(strict_types=1);

namespace WorldOS\Simulation\Infrastructure\Persistence;

use App\Models\UniverseModel;
use App\Models\UniverseSnapshot as UniverseSnapshotModel;
use WorldOS\Simulation\Domain\Engine\ValueObject\StateVector;
use WorldOS\Simulation\Domain\Engine\ValueObject\UniverseSnapshot;
use WorldOS\Simulation\Domain\Universe\Entity\Universe;
use WorldOS\Simulation\Domain\Universe\Repository\UniverseRepositoryInterface;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseId;
use WorldOS\Simulation\Domain\Universe\ValueObject\UniverseStatus;

/**
 * Eloquent implementation of UniverseRepositoryInterface.
 * Bridges the V5 Domain Universe entity to the Postgres DB via Laravel Eloquent.
 */
final class EloquentUniverseRepository implements UniverseRepositoryInterface
{
    public function findById(UniverseId $id): ?Universe
    {
        $model = UniverseModel::find($id->toString());

        if (!$model) {
            return null;
        }

        return $this->hydrate($model);
    }

    /**
     * @return Universe[]
     */
    public function findByGeneration(string $generationId): array
    {
        return UniverseModel::where('generation_id', $generationId)
            ->get()
            ->map(fn(UniverseModel $m) => $this->hydrate($m))
            ->all();
    }

    /**
     * @return Universe[]
     */
    public function findChildren(string $parentUniverseId): array
    {
        return UniverseModel::where('parent_universe_id', $parentUniverseId)
            ->get()
            ->map(fn(UniverseModel $m) => $this->hydrate($m))
            ->all();
    }

    /**
     * @return Universe[]
     */
    public function findByMultiverse(string $multiverseId): array
    {
        return UniverseModel::where('multiverse_id', $multiverseId)
            ->get()
            ->map(fn(UniverseModel $m) => $this->hydrate($m))
            ->all();
    }

    public function save(Universe $universe): void
    {
        $id = $universe->getId()->toString();

        UniverseModel::updateOrCreate(
            ['id' => $id],
            [
                'name'                => $universe->getName(),
                'status'              => $universe->getStatus()->value,
                'world_blueprint_id'  => $universe->getWorldBlueprintId(),
                'multiverse_id'       => $universe->getMultiverseId(),
                'current_tick'        => $universe->getCurrentTick(),
                'entropy'             => $universe->getEntropy(),
                'stability_index'     => $universe->getStabilityIndex(),
                'state_vector'        => $universe->getStateVector()->toArray(),
                
                // V6 Ontology Layers
                'culture_vector'      => $universe->getCulture()->toArray(),
                'ideology_vector'     => $universe->getIdeology()->toArray(),
                'lifecycle_state'     => $universe->getLifecycle()->value,
                'influence_mass'      => $universe->getInfluenceMass(),
                'stability_duration'  => $universe->getStabilityDuration(),
            ]
        );
    }

    public function saveWithSnapshot(Universe $universe, UniverseSnapshot $snapshot): void
    {
        $this->save($universe);

        UniverseSnapshotModel::create([
            'universe_id'      => $snapshot->universeId,
            'tick'             => $snapshot->tick,
            'seed'             => $snapshot->seed,
            'entropy'          => $snapshot->entropy,
            'stability_index'  => $snapshot->stabilityIndex,
            'existence_weight' => $snapshot->existenceWeight,
            'state_vector'     => $snapshot->stateVector->toArray(),
            'captured_at'      => $snapshot->capturedAt,
        ]);
    }

    public function delete(UniverseId $id): void
    {
        UniverseModel::where('id', $id->toString())->update(['status' => 'archived']);
        UniverseModel::destroy($id->toString());
    }

    /**
     * Hydrates a domain Universe entity from an Eloquent model.
     */
    private function hydrate(UniverseModel $model): Universe
    {
        $id = UniverseId::fromString($model->id);

        return Universe::restore(
            id:                   $id,
            name:                 $model->name,
            worldBlueprintId:     $model->world_blueprint_id ?? '',
            worldSignatureHash:   $model->world_signature_hash ?? '',
            multiverseId:         $model->multiverse_id ?? 'default',
            status:               UniverseStatus::from($model->status),
            currentTick:          (int) $model->current_tick,
            stateVector:          StateVector::fromArray($model->state_vector ?? []),
            ideology:             \WorldOS\Society\Faction\ValueObject\IdeologyVector::fromArray($model->ideology_vector ?? []),
            culture:              \WorldOS\Society\Culture\ValueObject\CulturalVector::fromArray($model->culture_vector ?? []),
            lifecycle:            \WorldOS\Core\ValueObject\LifecycleState::from($model->lifecycle_state ?? 'emerging'),
            influenceMass:        (float) ($model->influence_mass ?? 1.0),
            stabilityDuration:    (int) ($model->stability_duration ?? 0),
            generationId:         $model->generation_id,
            parentUniverseId:     $model->parent_universe_id,
            seedDna:              $model->seed_dna ?? [],
            fitnessTotalScore:    (float) $model->fitness_total_score,
            lifespan:             (int) $model->lifespan
        );
    }
}
