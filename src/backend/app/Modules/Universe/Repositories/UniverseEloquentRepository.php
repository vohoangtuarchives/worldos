<?php

declare(strict_types=1);

namespace App\Modules\Universe\Repositories;

use App\Models\UniverseModel;
use App\Modules\Universe\Contracts\UniverseRepositoryInterface;
use App\Modules\Universe\Entities\UniverseEntity;
use App\Modules\Universe\ValueObjects\UniverseId;
use App\Modules\Universe\ValueObjects\UniverseStatus;
use App\Modules\Shared\ValueObjects\CascadeStateVector;
use App\Modules\Shared\ValueObjects\Seed;
use App\Modules\Shared\ValueObjects\WorldStateVector;
use App\Modules\Universe\ValueObjects\WorldId;
use DateTimeImmutable;

/**
 * Eloquent implementation of UniverseRepositoryInterface.
 *
 * Maps between UniverseEntity (domain) and UniverseModel (Eloquent).
 * NO business logic — only data access and mapping.
 */
final class UniverseEloquentRepository implements UniverseRepositoryInterface
{
    public function save(UniverseEntity $entity): void
    {
        $model = UniverseModel::find($entity->getId()->value);

        if ($model === null) {
            $model = new UniverseModel();
            $model->id = $entity->getId()->value;
        }

        $model->world_id = $entity->getWorldId()->value;
        $model->state_vector = $entity->getStateVector()->toArray();
        $model->cascade_state = $entity->getCascadeState()->toArray();
        $model->current_tick = $entity->getCurrentTick();
        $model->age = $entity->getAge();
        $model->status = $entity->getStatus()->value;
        $model->random_seed = $entity->getSeed()->value;
        $model->parent_universe_id = $entity->getParentUniverseId()?->value;
        $model->target_tick = $entity->getTargetTick();
        $model->parameters = $entity->getParameters();
        $model->save();
    }

    public function findById(UniverseId $id): ?UniverseEntity
    {
        $model = UniverseModel::find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    /**
     * @return UniverseEntity[]
     */
    public function findByWorldId(WorldId $worldId): array
    {
        return UniverseModel::where('world_id', $worldId->value)
            ->get()
            ->map(fn (UniverseModel $m) => $this->modelToEntity($m))
            ->toArray();
    }

    /**
     * @return UniverseEntity[]
     */
    public function findByStatus(string $status): array
    {
        return UniverseModel::where('status', $status)
            ->get()
            ->map(fn (UniverseModel $m) => $this->modelToEntity($m))
            ->toArray();
    }

    /**
     * @return UniverseEntity[]
     */
    public function findForks(UniverseId $parentId): array
    {
        return UniverseModel::where('parent_universe_id', $parentId->value)
            ->get()
            ->map(fn (UniverseModel $m) => $this->modelToEntity($m))
            ->toArray();
    }

    private function modelToEntity(UniverseModel $model): UniverseEntity
    {
        return new UniverseEntity(
            id: UniverseId::fromString($model->id),
            worldId: WorldId::fromString($model->world_id),
            stateVector: WorldStateVector::fromArray($model->state_vector),
            cascadeState: CascadeStateVector::fromArray($model->cascade_state ?? []),
            seed: new Seed($model->random_seed),
            parentUniverseId: $model->parent_universe_id
                ? UniverseId::fromString($model->parent_universe_id)
                : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            currentTick: $model->current_tick,
            age: $model->age,
            status: UniverseStatus::from($model->status),
            targetTick: $model->target_tick,
            parameters: $model->parameters ?? [],
        );
    }
}
