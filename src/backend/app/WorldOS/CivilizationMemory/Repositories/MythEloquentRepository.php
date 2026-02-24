<?php

declare(strict_types=1);

namespace App\WorldOS\CivilizationMemory\Repositories;

use App\Models\WorldMythModel;
use App\WorldOS\CivilizationMemory\Contracts\MythRepositoryInterface;
use App\WorldOS\CivilizationMemory\Entities\WorldMythEntity;
use App\WorldOS\CivilizationMemory\ValueObjects\MythId;
use App\WorldOS\CivilizationMemory\ValueObjects\MythStrength;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use DateTimeImmutable;

class MythEloquentRepository implements MythRepositoryInterface
{
    public function findById(MythId $id): ?WorldMythEntity
    {
        $model = WorldMythModel::find($id->value);

        return $model ? $this->modelToEntity($model) : null;
    }

    public function save(WorldMythEntity $myth): void
    {
        $model = WorldMythModel::find($myth->getId()->value) ?? new WorldMythModel();

        $model->id = $myth->getId()->value;
        $model->universe_id = $myth->getUniverseId()->value;
        $model->theme = $myth->getTheme();
        $model->description = $myth->getDescription();
        $model->strength = $myth->getStrength()->value;
        $model->state = $myth->getState();
        $model->tick_emerged = $myth->getTickEmerged();
        $model->belief_sources = $myth->getBeliefSources();
        $model->save();
    }

    /**
     * @return WorldMythEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array
    {
        return WorldMythModel::where('universe_id', $universeId->value)
            ->get()
            ->map(fn(WorldMythModel $m) => $this->modelToEntity($m))
            ->all();
    }

    /**
     * @return WorldMythEntity[]
     */
    public function findActiveByUniverseId(UniverseId $universeId): array
    {
        return WorldMythModel::where('universe_id', $universeId->value)
            ->where('state', 'active')
            ->get()
            ->map(fn(WorldMythModel $m) => $this->modelToEntity($m))
            ->all();
    }

    private function modelToEntity(WorldMythModel $model): WorldMythEntity
    {
        return new WorldMythEntity(
            id: new MythId($model->id),
            universeId: new UniverseId($model->universe_id),
            theme: $model->theme,
            description: $model->description,
            strength: new MythStrength((float) $model->strength),
            tickEmerged: $model->tick_emerged,
            createdAt: $model->created_at instanceof DateTimeImmutable
                ? $model->created_at
                : new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            state: $model->state,
            beliefSources: $model->belief_sources ?? [],
        );
    }
}
