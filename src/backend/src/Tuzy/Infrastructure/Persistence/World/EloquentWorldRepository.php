<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\World;

use App\Models\World as WorldModel;
use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\World\Entity\World;
use Tuzy\Domain\World\Event\WorldCreated;
use Tuzy\Domain\World\Repository\WorldRepositoryInterface;

final class EloquentWorldRepository implements WorldRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = WorldModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = World::create($model->name ?? '', $model->id);
        }
        return $result;
    }

    public function findById(string $id): ?World
    {
        $model = WorldModel::find($id);
        if ($model === null) {
            return null;
        }
        return World::create($model->name, $model->id);
    }

    public function save(World $world): void
    {
        $model = WorldModel::find($world->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new WorldModel();
            $model->id = $world->getId();
            $model->preset = 'default';
            $model->gene_vector = [];
        }
        $model->name = $world->getName();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new WorldCreated($world->getId(), $world->getName()));
        }
    }
}
