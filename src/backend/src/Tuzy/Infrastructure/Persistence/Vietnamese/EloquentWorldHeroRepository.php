<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Vietnamese;

use App\Domains\Vietnamese\Models\WorldHero as WorldHeroModel;
use Illuminate\Contracts\Events\Dispatcher;
use Tuzy\Domain\Vietnamese\Entity\WorldHero;
use Tuzy\Domain\Vietnamese\Event\WorldHeroCreated;
use Tuzy\Domain\Vietnamese\Repository\WorldHeroRepositoryInterface;

final class EloquentWorldHeroRepository implements WorldHeroRepositoryInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
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
