<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Repositories;

use App\Models\WorldScarModel;
use App\WorldOS\CivilizationMemory\Contracts\ScarRepositoryInterface;
use App\WorldOS\CivilizationMemory\Entities\WorldScarEntity;
use App\WorldOS\CivilizationMemory\ValueObjects\ScarId;
use App\WorldOS\CivilizationMemory\ValueObjects\ScarWeight;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use DateTimeImmutable;

class ScarEloquentRepository implements ScarRepositoryInterface
{
    public function findById(ScarId $id): ?WorldScarEntity
    {
        $model = WorldScarModel::find($id->value);

        return $model ? $this->modelToEntity($model) : null;
    }

    public function save(WorldScarEntity $scar): void
    {
        $model = WorldScarModel::find($scar->getId()->value) ?? new WorldScarModel();

        $model->id = $scar->getId()->value;
        $model->universe_id = $scar->getUniverseId()->value;
        $model->source_event = $scar->getSourceEvent();
        $model->type = $scar->getType();
        $model->weight = $scar->getWeight()->value;
        $model->description = $scar->getDescription();
        $model->tick_created = $scar->getTickCreated();
        $model->current_intensity = $scar->getCurrentIntensity();
        $model->save();
    }

    /**
     * @return WorldScarEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array
    {
        return WorldScarModel::where('universe_id', $universeId->value)
            ->get()
            ->map(fn(WorldScarModel $m) => $this->modelToEntity($m))
            ->all();
    }

    public function calculateTotalPressure(UniverseId $universeId, int $currentTick): float
    {
        $scars = $this->findByUniverseId($universeId);

        return array_reduce(
            $scars,
            fn(float $total, WorldScarEntity $scar) => $total + $scar->calculatePressure($currentTick),
            0.0
        );
    }

    private function modelToEntity(WorldScarModel $model): WorldScarEntity
    {
        return new WorldScarEntity(
            id: new ScarId($model->id),
            universeId: new UniverseId($model->universe_id),
            sourceEvent: $model->source_event,
            type: $model->type,
            weight: new ScarWeight($model->weight),
            description: $model->description,
            tickCreated: $model->tick_created,
            createdAt: $model->created_at instanceof DateTimeImmutable
                ? $model->created_at
                : new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            currentIntensity: (float) $model->current_intensity,
        );
    }
}
