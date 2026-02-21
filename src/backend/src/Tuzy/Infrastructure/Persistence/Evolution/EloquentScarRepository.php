<?php

namespace Tuzy\Infrastructure\Persistence\Evolution;

use Tuzy\Domain\Evolution\Repository\ScarRepository;
use Tuzy\Domain\Evolution\Entity\Scar;
use App\Models\WorldScar as ScarModel;

class EloquentScarRepository implements ScarRepository
{
    public function save(Scar $scar): void
    {
        ScarModel::updateOrCreate(
            ['id' => $scar->getId()],
            [
                'world_id' => $scar->getWorldId(),
                'type' => $scar->getType(),
                'magnitude' => $scar->getMagnitude(),
                'inflicted_at' => $scar->getInflictedAt()
            ]
        );
    }

    public function findByWorld(string $worldId): array
    {
        $models = ScarModel::where('world_id', $worldId)
            ->where('magnitude', '>', 0)
            ->get();
            
        return $models->map(function($model) {
            return new Scar(
                $model->id,
                $model->world_id,
                $model->type,
                $model->magnitude,
                new \DateTimeImmutable($model->inflicted_at ?? 'now')
            );
        })->all();
    }
}
