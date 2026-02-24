<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\Heroes;

use WorldOS\Legacy\Domain\Vietnamese\Models\Hero as HeroModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Saga\Domain\Hero\Entity\Hero;
use WorldOS\Saga\Domain\Hero\Event\HeroCreated;
use WorldOS\Saga\Domain\Hero\Repository\HeroRepositoryInterface;

final class EloquentHeroRepository implements HeroRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    /** @inheritDoc */
    public function findAll(): array
    {
        $models = HeroModel::orderBy('updated_at', 'desc')->get();
        $result = [];
        foreach ($models as $model) {
            $result[] = Hero::create($model->name ?? '', (string) $model->world_id, $model->id);
        }
        return $result;
    }

    public function findById(string $id): ?Hero
    {
        $model = HeroModel::find($id);
        if ($model === null) {
            return null;
        }
        return Hero::create(
            $model->name ?? '',
            (string) $model->world_id,
            $model->id,
        );
    }

    public function save(Hero $Hero): void
    {
        $model = HeroModel::find($Hero->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new HeroModel();
            $model->id = $Hero->getId();
            $model->world_id = $Hero->getWorldId();
            $model->archetype = 'hero';
            $model->status = 'active';
        }
        $model->name = $Hero->getName();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new HeroCreated(
                $Hero->getId(),
                $Hero->getName(),
                $Hero->getWorldId(),
            ));
        }
    }
}
