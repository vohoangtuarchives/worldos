<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\Saga;

use WorldOS\Saga\Domain\Legacy\Saga as SagaModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Saga\Domain\Legacy\Entity\Saga;
use WorldOS\Saga\Domain\Legacy\Event\SagaCreated;
use WorldOS\Saga\Domain\Legacy\Repository\SagaRepositoryInterface;

final class EloquentSagaRepository implements SagaRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = SagaModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = Saga::create($model->name ?? '', $model->id);
        }
        return $result;
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
