<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Runtime;

use App\Models\UniverseModel;
use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\Runtime\Entity\Universe;
use Tuzy\Domain\Runtime\Event\UniverseCreated;
use Tuzy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class EloquentUniverseRepository implements UniverseRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function findById(string $id): ?Universe
    {
        $model = UniverseModel::find($id);
        if ($model === null) {
            return null;
        }
        return Universe::create($model->name ?? '', $model->id);
    }

    public function save(Universe $universe): void
    {
        $model = UniverseModel::find($universe->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new UniverseModel();
            $model->id = $universe->getId();
            $model->state_vector = [];
            $model->parameters = [];
            $model->age = 0;
        }
        $model->name = $universe->getName();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new UniverseCreated($universe->getId(), $universe->getName()));
        }
    }
}
