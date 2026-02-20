<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Saga;

use App\Domains\Saga\Saga as SagaModel;
use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\Saga\Entity\Saga;
use Tuzy\Domain\Saga\Event\SagaCreated;
use Tuzy\Domain\Saga\Repository\SagaRepositoryInterface;

final class EloquentSagaRepository implements SagaRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

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
        $isNew = $model === null;
        if ($model === null) {
            $model = new SagaModel();
            $model->id = $saga->getId();
            $model->world_count = 0;
            $model->status = SagaModel::STATUS_PENDING;
        }
        $model->name = $saga->getName();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new SagaCreated($saga->getId(), $saga->getName()));
        }
    }
}
