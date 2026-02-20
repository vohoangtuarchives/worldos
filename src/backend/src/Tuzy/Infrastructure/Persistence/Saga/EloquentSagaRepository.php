<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Saga;

use App\Domains\Saga\Saga as SagaModel;
use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class EloquentSagaRepository implements SagaRepositoryInterface
{
    public function findById(string $id): ?Saga
    {
        $model = SagaModel::find($id);
        if ($model === null) {
            return null;
        }
        return Saga::create($model->name ?? '', $model->id);
    }

    public function save(Saga $saga): void
    {
        $model = SagaModel::find($saga->getId());
        if ($model === null) {
            $model = new SagaModel();
            $model->id = $saga->getId();
            $model->world_count = 0;
            $model->status = SagaModel::STATUS_PENDING;
        }
        $model->name = $saga->getName();
        $model->save();
    }
}
