<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\Runtime;

use App\Models\UniverseModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Legacy\Domain\Runtime\Entity\Universe;
use WorldOS\Legacy\Domain\Runtime\Event\UniverseCreated;
use WorldOS\Legacy\Domain\Runtime\Repository\UniverseRepositoryInterface;

final class EloquentUniverseRepository implements UniverseRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = UniverseModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = Universe::create(
                $model->name ?? '',
                $model->world_id ?? '',
                $model->saga_id ?? '',
                $model->id,
                (int)($model->age ?? 0),
                $model->status ?? 'running',
                $model->state_vector ?? [],
                (float)($model->entropy ?? 0.0),
                (float)($model->stability_index ?? 1.0)
            );
        }
        return $result;
    }

    public function findById(string $id): ?Universe
    {
        $model = UniverseModel::find($id);
        if ($model === null) {
            return null;
        }
        return Universe::create(
            $model->name ?? '',
            $model->world_id ?? '',
            $model->saga_id ?? '',
            $model->id,
            (int)($model->age ?? 0),
            $model->status ?? 'running',
            $model->state_vector ?? [],
            (float)($model->entropy ?? 0.0),
            (float)($model->stability_index ?? 1.0)
        );
    }

    /** @inheritDoc */
    public function findByWorldId(string $worldId): array
    {
        $models = UniverseModel::where('world_id', $worldId)->orderBy('created_at', 'asc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = Universe::create(
                $model->name ?? '',
                $model->world_id ?? '',
                $model->saga_id ?? '',
                $model->id,
                (int)($model->age ?? 0),
                $model->status ?? 'running',
                $model->state_vector ?? [],
                (float)($model->entropy ?? 0.0),
                (float)($model->stability_index ?? 1.0)
            );
        }
        return $result;
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
        $model->world_id = $universe->getWorldId(); // Ensure mapping
        $model->saga_id = $universe->getSagaId(); // Ensure mapping
        $model->status = $universe->getStatus();
        $model->age = $universe->getAge();
        $model->state_vector = $universe->getStateVector();
        $model->entropy = $universe->getEntropy();
        $model->stability_index = $universe->getStabilityIndex();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new UniverseCreated($universe->getId(), $universe->getName()));
        }
    }

    public function delete(string $id): void
    {
        UniverseModel::destroy($id);
    }
}
