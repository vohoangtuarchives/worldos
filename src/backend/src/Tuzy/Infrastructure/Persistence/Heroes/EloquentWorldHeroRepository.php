<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Heroes;

use Tuzy\Domain\Vietnamese\Models\WorldHero as WorldHeroModel;
use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\Heroes\Entity\WorldHero;
use Tuzy\Domain\Heroes\Event\WorldHeroCreated;
use Tuzy\Domain\Heroes\Repository\WorldHeroRepositoryInterface;

final class EloquentWorldHeroRepository implements WorldHeroRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = WorldHeroModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = WorldHero::create($model->name ?? '', (string) $model->world_id, $model->id);
        }
        return $result;
    }

    public function findById(string $id): ?WorldHero
    {
        $model = WorldHeroModel::find($id);
        if ($model === null) {
            return null;
        }
        return WorldHero::create(
            $model->name ?? '',
            (string) $model->world_id,
            $model->id,
        );
    }

    public function save(WorldHero $worldHero): void
    {
        $model = WorldHeroModel::find($worldHero->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new WorldHeroModel();
            $model->id = $worldHero->getId();
            $model->world_id = $worldHero->getWorldId();
            $model->archetype = 'hero';
            $model->status = 'active';
        }
        $model->name = $worldHero->getName();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new WorldHeroCreated(
                $worldHero->getId(),
                $worldHero->getName(),
                $worldHero->getWorldId(),
            ));
        }
    }
}
