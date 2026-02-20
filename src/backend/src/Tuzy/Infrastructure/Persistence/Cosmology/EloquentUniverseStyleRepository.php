<?php

declare(strict_types=1);

namespace Tuzy\Infrastructure\Persistence\Cosmology;

use App\Models\UniverseStyle as UniverseStyleModel;
use Tuzy\Domain\Cosmology\Entity\UniverseStyle;
use Tuzy\Domain\Cosmology\Repository\UniverseStyleRepositoryInterface;

final class EloquentUniverseStyleRepository implements UniverseStyleRepositoryInterface
{
    public function findById(string $id): ?UniverseStyle
    {
        $model = UniverseStyleModel::find($id);
        if ($model === null) {
            return null;
        }
        return UniverseStyle::create(
            $model->name ?? '',
            (string) $model->world_id,
            $model->id,
        );
    }

    public function save(UniverseStyle $universeStyle): void
    {
        $model = UniverseStyleModel::find($universeStyle->getId());
        if ($model === null) {
            $model = new UniverseStyleModel();
            $model->id = $universeStyle->getId();
            $model->world_id = $universeStyle->getWorldId();
            $model->style_vector = [];
            $model->version = 1;
            $model->is_active = true;
        }
        $model->name = $universeStyle->getName();
        $model->save();
    }
}
