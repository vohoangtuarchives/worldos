<?php

declare(strict_types=1);

namespace App\WorldOS\World\Repositories;

use App\Models\World;
use App\WorldOS\Shared\ValueObjects\LawVector;
use App\WorldOS\World\Contracts\WorldRepositoryInterface;
use App\WorldOS\World\Entities\WorldEntity;
use App\WorldOS\World\ValueObjects\WorldId;
use App\WorldOS\World\ValueObjects\WorldStatus;
use DateTimeImmutable;

/**
 * Eloquent implementation of WorldRepositoryInterface.
 *
 * Maps between WorldEntity (domain) and World (Eloquent model).
 * NO business logic here — only data access and mapping.
 */
final class WorldEloquentRepository implements WorldRepositoryInterface
{
    public function save(WorldEntity $entity): void
    {
        $model = World::find($entity->getId()->value);

        if ($model === null) {
            $model = new World();
            $model->id = $entity->getId()->value;
        }

        $model->name = $entity->getName();
        $model->law_vector = $entity->getLawVector()->toArray();
        $model->preset_key = $entity->getPresetKey();
        $model->origin_type = $entity->getOriginType();
        $model->status = $entity->getStatus()->value;
        $model->save();
    }

    public function findById(WorldId $id): ?WorldEntity
    {
        $model = World::find($id->value);

        if ($model === null) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    /**
     * @return WorldEntity[]
     */
    public function findAll(): array
    {
        return World::all()
            ->map(fn (World $model) => $this->modelToEntity($model))
            ->toArray();
    }

    /**
     * @return WorldEntity[]
     */
    public function findByStatus(string $status): array
    {
        return World::where('status', $status)
            ->get()
            ->map(fn (World $model) => $this->modelToEntity($model))
            ->toArray();
    }

    private function modelToEntity(World $model): WorldEntity
    {
        return new WorldEntity(
            id: WorldId::fromString($model->id),
            name: $model->name,
            lawVector: LawVector::fromArray($model->law_vector),
            presetKey: $model->preset_key,
            originType: $model->origin_type,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            status: WorldStatus::from($model->status),
        );
    }
}
