<?php

declare(strict_types=1);

namespace App\WorldOS\Runtime\Repositories;

use App\Models\UniverseSnapshotModel;
use App\WorldOS\Runtime\Contracts\UniverseSnapshotRepositoryInterface;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use App\WorldOS\Runtime\ValueObjects\UniverseSnapshot;
use App\WorldOS\Shared\ValueObjects\CascadeStateVector;
use App\WorldOS\Shared\ValueObjects\StabilityMetric;
use App\WorldOS\Shared\ValueObjects\WorldStateVector;
use DateTimeImmutable;

/**
 * Eloquent implementation of UniverseSnapshotRepositoryInterface.
 *
 * NO business logic — only data access and mapping.
 */
final class UniverseSnapshotEloquentRepository implements UniverseSnapshotRepositoryInterface
{
    public function save(UniverseSnapshot $snapshot): void
    {
        $model = new UniverseSnapshotModel();
        $model->universe_id = $snapshot->universeId->value;
        $model->tick = $snapshot->tick;
        $model->state_vector = $snapshot->stateVector->toArray();
        $model->cascade_state = $snapshot->cascadeState?->toArray();
        $model->stability_metric = $snapshot->stability?->value;
        $model->entropy = $snapshot->stateVector->entropy;
        $model->metrics = $snapshot->metrics;
        $model->save();
    }

    public function findByTick(UniverseId $universeId, int $tick): ?UniverseSnapshot
    {
        $model = UniverseSnapshotModel::where('universe_id', $universeId->value)
            ->where('tick', $tick)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->modelToSnapshot($model);
    }

    public function findLatest(UniverseId $universeId): ?UniverseSnapshot
    {
        $model = UniverseSnapshotModel::where('universe_id', $universeId->value)
            ->orderByDesc('tick')
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->modelToSnapshot($model);
    }

    /**
     * @return UniverseSnapshot[]
     */
    public function findAllByUniverse(UniverseId $universeId): array
    {
        return UniverseSnapshotModel::where('universe_id', $universeId->value)
            ->orderBy('tick')
            ->get()
            ->map(fn (UniverseSnapshotModel $m) => $this->modelToSnapshot($m))
            ->toArray();
    }

    /**
     * @return UniverseSnapshot[]
     */
    public function findByTickRange(UniverseId $universeId, int $fromTick, int $toTick): array
    {
        return UniverseSnapshotModel::where('universe_id', $universeId->value)
            ->whereBetween('tick', [$fromTick, $toTick])
            ->orderBy('tick')
            ->get()
            ->map(fn (UniverseSnapshotModel $m) => $this->modelToSnapshot($m))
            ->toArray();
    }

    private function modelToSnapshot(UniverseSnapshotModel $model): UniverseSnapshot
    {
        return new UniverseSnapshot(
            universeId: UniverseId::fromString($model->universe_id),
            tick: $model->tick,
            stateVector: WorldStateVector::fromArray($model->state_vector),
            cascadeState: $model->cascade_state
                ? CascadeStateVector::fromArray($model->cascade_state)
                : null,
            stability: $model->stability_metric !== null
                ? new StabilityMetric($model->stability_metric)
                : null,
            metrics: $model->metrics,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
        );
    }
}
