<?php

declare(strict_types=1);

namespace App\Modules\Narrative\Repositories;

use App\Models\SagaModel;
use App\Models\SagaWorldModel;
use App\Modules\Narrative\Contracts\SagaRepositoryInterface;
use App\Modules\Narrative\Entities\SagaEntity;
use App\Modules\Narrative\ValueObjects\SagaId;
use App\Modules\Narrative\ValueObjects\SagaStatus;
use DateTimeImmutable;

/**
 * Saga Eloquent Repository — infrastructure layer.
 */
class SagaEloquentRepository implements SagaRepositoryInterface
{
    public function findById(SagaId $id): ?SagaEntity
    {
        $model = SagaModel::with('worlds')->find($id->value);

        if (!$model) {
            return null;
        }

        return $this->modelToEntity($model);
    }

    public function save(SagaEntity $saga): void
    {
        $model = SagaModel::find($saga->getId()->value) ?? new SagaModel();

        $model->id = $saga->getId()->value;
        $model->name = $saga->getName();
        $model->preset_key = $saga->getPresetKey();
        $model->status = $saga->getStatus()->value;
        $model->created_at = $saga->getCreatedAt();
        $model->save();

        // Sync world links
        $existingIds = SagaWorldModel::where('saga_id', $saga->getId()->value)
            ->pluck('universe_id')
            ->toArray();

        foreach ($saga->getWorlds() as $world) {
            if (!in_array($world['universe_id'], $existingIds)) {
                SagaWorldModel::create([
                    'saga_id' => $saga->getId()->value,
                    'world_id' => $world['world_id'],
                    'universe_id' => $world['universe_id'],
                    'sequence' => $world['sequence'],
                ]);
            }
        }
    }

    /**
     * @return SagaEntity[]
     */
    public function findActive(): array
    {
        $models = SagaModel::with('worlds')
            ->where('status', SagaStatus::ACTIVE->value)
            ->get();

        return $models->map(fn(SagaModel $m) => $this->modelToEntity($m))->all();
    }

    private function modelToEntity(SagaModel $model): SagaEntity
    {
        $worlds = $model->worlds->map(fn(SagaWorldModel $w) => [
            'world_id' => $w->world_id,
            'universe_id' => $w->universe_id,
            'sequence' => $w->sequence,
        ])->all();

        return new SagaEntity(
            id: new SagaId($model->id),
            name: $model->name,
            presetKey: $model->preset_key,
            createdAt: $model->created_at instanceof DateTimeImmutable
                ? $model->created_at
                : new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            status: SagaStatus::from($model->status),
            worlds: $worlds,
        );
    }
}
