<?php

declare(strict_types=1);

namespace App\WorldOS\Attractor\Repositories;

use App\Models\AttractorModel;
use App\WorldOS\Attractor\Contracts\AttractorRepositoryInterface;
use App\WorldOS\Attractor\Entities\AttractorEntity;
use App\WorldOS\Attractor\ValueObjects\AttractorId;
use App\WorldOS\Attractor\ValueObjects\AttractorStatus;
use App\WorldOS\Attractor\ValueObjects\AttractorType;
use App\WorldOS\Runtime\ValueObjects\UniverseId;
use DateTimeImmutable;

/**
 * Attractor Eloquent Repository — infrastructure layer.
 */
class AttractorEloquentRepository implements AttractorRepositoryInterface
{
    public function findById(AttractorId $id): ?AttractorEntity
    {
        $model = AttractorModel::find($id->value);

        return $model ? $this->modelToEntity($model) : null;
    }

    public function save(AttractorEntity $attractor): void
    {
        $model = AttractorModel::find($attractor->getId()->value) ?? new AttractorModel();

        $model->id = $attractor->getId()->value;
        $model->universe_id = $attractor->getUniverseId()->value;
        $model->type = $attractor->getType()->value;
        $model->magnitude = $attractor->getMagnitude();
        $model->basin_depth = $attractor->getBasinDepth();
        $model->activation_threshold = $attractor->getActivationThreshold();
        $model->status = $attractor->getStatus()->value;
        $model->current_pull = $attractor->getCurrentPull();
        $model->created_at = $attractor->getCreatedAt();
        $model->save();
    }

    /**
     * @return AttractorEntity[]
     */
    public function findByUniverseId(UniverseId $universeId): array
    {
        return AttractorModel::where('universe_id', $universeId->value)
            ->get()
            ->map(fn(AttractorModel $m) => $this->modelToEntity($m))
            ->all();
    }

    /**
     * @return AttractorEntity[]
     */
    public function findActiveByUniverseId(UniverseId $universeId): array
    {
        return AttractorModel::where('universe_id', $universeId->value)
            ->whereIn('status', [AttractorStatus::ACTIVE->value, AttractorStatus::CAPTURED->value])
            ->get()
            ->map(fn(AttractorModel $m) => $this->modelToEntity($m))
            ->all();
    }

    private function modelToEntity(AttractorModel $model): AttractorEntity
    {
        return new AttractorEntity(
            id: new AttractorId($model->id),
            universeId: new UniverseId($model->universe_id),
            type: AttractorType::from($model->type),
            magnitude: (float) $model->magnitude,
            basinDepth: (float) $model->basin_depth,
            activationThreshold: (float) $model->activation_threshold,
            createdAt: $model->created_at instanceof DateTimeImmutable
                ? $model->created_at
                : new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            status: AttractorStatus::from($model->status),
            currentPull: (float) $model->current_pull,
        );
    }
}
