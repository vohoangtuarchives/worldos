<?php

declare(strict_types=1);

namespace WorldOS\Legacy\Infrastructure\Persistence\World;

use App\Models\World as WorldModel;
use Illuminate\Contracts\Events\Dispatcher;
use WorldOS\Blueprint\Domain\Legacy\Entity\World;
use WorldOS\Blueprint\Domain\Legacy\Event\WorldCreated;
use WorldOS\Blueprint\Domain\Legacy\Repository\WorldRepositoryInterface;

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
            $result[] = World::create(
                $model->name ?? '',
                $model->id,
                $model->status ?? 'unknown',
                $model->health_status ?? 'unknown',
                (int)($model->current_tick ?? 0),
                $model->origin_type ?? 'unknown',
                $model->preset ?? 'default',
                is_array($model->config) ? $model->config : (json_decode($model->config ?? '{}', true) ?? []),
                is_array($model->gene_vector) ? $model->gene_vector : (json_decode($model->gene_vector ?? '{}', true) ?? [])
            );
        }
        return $result;
    }

    public function findById(string $id): ?World
    {
        $model = WorldModel::find($id);
        if ($model === null) {
            return null;
        }
        return World::create(
            $model->name,
            $model->id,
            $model->status ?? 'unknown',
            $model->health_status ?? 'unknown',
            (int)($model->current_tick ?? 0),
            $model->origin_type ?? 'unknown',
            $model->preset ?? 'default',
            is_array($model->config) ? $model->config : (json_decode($model->config ?? '{}', true) ?? []),
            is_array($model->gene_vector) ? $model->gene_vector : (json_decode($model->gene_vector ?? '{}', true) ?? [])
        );
    }

    public function save(World $world): void
    {
        $model = WorldModel::find($world->getId());
        $isNew = $model === null;
        if ($model === null) {
            $model = new WorldModel();
            $model->id = $world->getId();
        }
        $model->name = $world->getName();
        $model->status = $world->getStatus();
        $model->health_status = $world->getHealthStatus();
        $model->current_tick = $world->getCurrentTick();
        $model->origin_type = $world->getOriginType();
        $model->preset = $world->getPreset();
        $model->config = $world->getConfig();
        $model->gene_vector = $world->getGeneVector();
        $model->save();

        if ($isNew) {
            $this->dispatcher->dispatch(new WorldCreated($world->getId(), $world->getName()));
        }
    }

    public function delete(string $id): void
    {
        WorldModel::destroy($id);
    }
}
